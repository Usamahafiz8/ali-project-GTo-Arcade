/**
 * RedisBridge.js — Redis Cache Layer & Player Finance Engine (Fishing Game Server)
 *
 * This is the most critical module in the fishing game server. It acts as the bridge
 * between the in-memory game state, Redis cache, and the MySQL database for all
 * financial operations (balances, bets, wins).
 *
 * WHY REDIS?
 * MySQL cannot handle hundreds of balance read/write operations per second from a
 * real-time game. Redis stores every player's live balance, bet totals, and win totals
 * in memory for microsecond-speed access. MySQL is only written to periodically
 * (via synchronizePlayerToDB) or on significant events — not on every bullet fired.
 *
 * KEY RESPONSIBILITIES:
 *   1. Balance Sync    — reads player balance from DB on join, writes back periodically
 *   2. Bet Tracking    — increments per-player bet counters in Redis on each shot
 *   3. Win Tracking    — increments per-player win counters in Redis on each catch
 *   4. DB Flush        — periodically flushes Redis counters → w_stat_game in MySQL
 *   5. Bonus Progress  — updates wagering requirements for active player bonuses
 *   6. Admin Sync      — detects balance changes made by admin in DB and reconciles them
 *   7. RTP Gating      — isWinningWave() gates whether the player can catch fish
 *
 * REDIS KEY SCHEMA (all prefixed by player userId):
 *   player_balance_{id}      — current live balance (in dollars, not cents)
 *   player_last_balance_{id} — balance snapshot from last DB sync (for delta detection)
 *   player_bet_{id}          — bet amount accumulated since last DB flush
 *   player_total_bet_{id}    — lifetime total bet (session-scoped, used for RTP gating)
 *   player_win_{id}          — win amount accumulated since last DB flush
 *   player_total_win_{id}    — lifetime total win (session-scoped, used for RTP gating)
 */

const redis = require('ioredis');
const { lock } = require('simple-redis-mutex');
const { Common } = require('./Common');
const mysql_tools = require('./mysql_tools');
const { toFloat, getRandomInt, in_array } = require('./utils');
var dbconn = require('./DBConn').dbconn;
var client;                          // Module-level Redis client, shared across all instances
var RedisLock = 'RedisLock';         // Mutex key used for distributed locking (prevents race conditions)

class RedisBridge{
    constructor(){
        // Redis key prefixes — all player data is namespaced by userId
        this.SHOP_KEY = 'shop_';
        this.PLAYER_BET_KEY = 'player_bet_';                   // Short-term bet accumulator (flushed to DB)
        this.PLAYER_TOTAL_BET_KEY = 'player_total_bet_';       // Session total bet (used for RTP gating)
        this.PLAYER_WIN_KEY = 'player_win_';                   // Short-term win accumulator (flushed to DB)
        this.PLAYER_TOTAL_WIN_KEY = 'player_total_win_';       // Session total win (used for RTP gating)
        this.PLAYER_BALANCE_KEY = 'player_balance_';           // Live balance in dollars (high-frequency R/W)
        this.PLAYER_LAST_BALANCE_KEY = 'player_last_balance_'; // Balance at last DB sync (for admin change detection)

        // RTP control thresholds (used by other modules, kept here as config)
        this.percent = 80;        // Target RTP percentage for skill/boss fish
        this.normalPercent = 30;  // Target RTP percentage for normal fish

        // Connect to local Redis with password authentication
        client = redis.createClient(
        {
            password: 'thisisr3disp@ssword'
        });

        client.on('error', error=>{
            console.error(error);
        });

        client.on('connect', async ()=>{
            console.log('Reddis service connected');
        });

        this.startTime = new Date();
        this.isRun = true;
    }

    /**
     * get total bet/win value of player
     * @param {*Player instance} player 
     */
    async getBetWin(player)
    {
        var key1 = this.PLAYER_TOTAL_WIN_KEY + player.userId;
        var key2 = this.PLAYER_TOTAL_BET_KEY + player.userId;

        var total_win = await client.get(key1);
        var total_bet = await client.get(key2);
        if(total_win == undefined || total_bet == undefined)
        {
            player.total_win = 0;        
            player.total_bet = 0;  
            client.set(key1, player.total_win);
            client.set(key2, player.total_bet);                
        }
        else
        {
            player.total_bet = toFloat(total_bet);
            player.total_win = toFloat(total_win);
        }
    }

    /**
     * update bank value when bet
     * @param {Player instance} player     
     */
    async updateBank(player, bet_amount)
    {
        bet_amount *= 0.01;

        //update player bet value
        var key = this.PLAYER_BET_KEY + player.userId;
        var betData = await client.get(key);      
        if(betData == null || betData == 'NaN')
        {
            client.set(key, 0);
            betData = 0;
        }  
        client.set(key, toFloat(betData) + bet_amount);
        // console.log("player bet amount: " + (toFloat(betData) + bet_amount));
        
        key = this.PLAYER_TOTAL_BET_KEY + player.userId;
        var total_bet = await client.get(key);      
        if(total_bet == null || total_bet == 'NaN')
        {
            client.set(key, player.total_bet);
            total_bet = player.total_bet;
        }  
        client.set(key, toFloat(total_bet) + bet_amount);
        player.total_bet += bet_amount;        
        // console.log("player bet amount: " + player.total_bet);
    }

    /**
     * Change bank value when win
     * @param {Player instance} player 
     * @param {normal bank value} bank 
     * @param {skill bank value} skillBank 
     */
    async updateBankDirect(player, normal, skill)
    {        
        //update player win value
        var key = this.PLAYER_WIN_KEY + player.userId;
        var win = await client.get(key);
        if(win == null || win == 'NaN')
        {
            client.set(key, 0);
            win = 0;
        }
        win = (toFloat(win) + toFloat(normal) + toFloat(skill)).toFixed(2);
        client.set(key, win);

        key = this.PLAYER_TOTAL_WIN_KEY + player.userId;
        var total_win = await client.get(key);
        if(total_win == null || total_win == 'NaN')
        {
            client.set(key, player.total_win);
            total_win = player.total_win;
        }
        total_win = (toFloat(total_win) + toFloat(normal) + toFloat(skill)).toFixed(2);
        client.set(key, total_win);
        player.total_win += toFloat(normal) + toFloat(skill);
    }

    /**
     * save player data from redis cahce to database
     * @param {Player instance} player 
     */
    async synchronizePlayerToDB(player)
    {
        //save player balance
        var key = this.PLAYER_BALANCE_KEY + player.userId;
        var balance = toFloat(await client.get(key));
        var result = await mysql_tools.sendQuery(dbconn, "SELECT balance FROM w_users WHERE id = ?", [player.userId]);
        if(result.length > 0)
        {
            var db_balance = toFloat(result[0]['balance']);
            key = this.PLAYER_LAST_BALANCE_KEY + player.userId;
            var last_balance = toFloat(await client.get(key));
            
            // console.log("synchronizing balance to db, last_balance_db: " + last_balance + " current_balance_db: " + db_balance + " cur balance: " + balance + " new balance: " + (balance + db_balance - last_balance));
            balance += (db_balance - last_balance); //if db's balance is changed by admin, consider that delta value and change the player's balance
            client.set(this.PLAYER_BALANCE_KEY + player.userId, balance);
            client.set(this.PLAYER_LAST_BALANCE_KEY + player.userId, balance);
            player.balance = balance * 100;
            mysql_tools.sendQuery(dbconn, "UPDATE w_users SET balance = ?, summon = ?, freeze = ? WHERE id = ?", [ balance, player.summon, player.freeze, player.userId ]);
        }        

        //player bet for saving time
        key = this.PLAYER_BET_KEY + player.userId;
        var value = await client.get(key);
        if(value == null)
        {
            client.set(key, 0);
            value = 0;
        }
        var bet = toFloat(value).toFixed(2);

        //player win for saving time
        key = this.PLAYER_WIN_KEY + player.userId;
        value = await client.get(key);
        if(value == null)
        {
            client.set(key, 0);
            value = 0;
        }
        var win = toFloat(value).toFixed(2);

        //save player bet/win statistics
        if(bet > 0 || win > 0)
        {
            key = this.PLAYER_BET_KEY + player.userId;
            client.set(key, 0);
            key = this.PLAYER_WIN_KEY + player.userId;
            client.set(key, 0);
            if(player.is_demo == 0)
            {

                var winInfo = JSON.stringify(player.winFish);
                player.winFish = [];
                mysql_tools.sendQuery(dbconn, "INSERT INTO w_stat_game (user_id, balance, bet, win, game, game_name, shop_id, category, info) VALUES (?,?,?,?,?,?,?,?,?)", [player.id, balance, bet, win, player.gameName + "PGD", player.gameName + "PGD", player.real_shop_id, 2, winInfo]);
            }

            //update bonus info
            var bonuses = await mysql_tools.sendQuery(dbconn, "SELECT * FROM w_bonuses WHERE user_id = ? and (subtype = 1 or type = 0)", [player.userId]);
            if(bonuses.length > 0)
            {
                for(var i = 0; i < bonuses.length; i++)
                {
                    var bonus = bonuses[i];
                    if(bonus.available_step < bonus.total_step)
                    {
                        var step_progress = parseFloat(bonus.step_progress) + toFloat(bet);
                        var available_step = bonus.available_step;
                        if(step_progress > bonus.step_size)
                        {
                            step_progress = 0;
                            available_step++;
                            if(available_step > bonus.total_step)
                                available_step = bonus.total_step;
                        }

                        await mysql_tools.sendQuery(dbconn, "UPDATE w_bonuses set available_step = ?, step_progress = ? where id = ?", [available_step, step_progress, bonus.id]);
                    }
                }
                
            }
        }        
    }

    /**
     * Pushes the player's current in-memory balance back into Redis.
     * Called after every successful fish catch to keep the cache up-to-date.
     * NOTE: balance is stored in cents internally (×100), Redis stores dollars (/100).
     *
     * @param {Player} player
     */
    async updateBalance(player)
    {
        var key = this.PLAYER_BALANCE_KEY + player.userId;
        var value = client.get(key);
        if(value != undefined)
        {
            client.set(key, player.balance / 100); // Convert cents → dollars for storage
        }
    }

    /**
     * Sets BOTH the live balance key AND the "last balance" snapshot in Redis.
     * Called when a player first joins a game room so that admin-change detection
     * (in synchronizePlayerToDB) has a valid baseline to compare against.
     *
     * @param {Player} player
     */
    async setLastBalance(player)
    {
        var key = this.PLAYER_LAST_BALANCE_KEY + player.userId;
        client.set(key, player.balance / 100); // Snapshot for future delta detection
        key = this.PLAYER_BALANCE_KEY + player.userId;
        client.set(key, player.balance / 100); // Live balance cache
    }

    /**
     * Retrieves a shared fish cannon angle stored in Redis.
     * Used to synchronize the shooting angle across all players in a room.
     *
     * @returns {Promise<string>} - The current fish angle value
     */
    async getAngle()
    {
        var key = "FISH_ANGLE";
        var value = await client.get(key);
        return value;
    }

    /**
     * Flags the bridge as stopped (used for graceful shutdown).
     */
    stop()
    {
        this.isRun = false;
    }

    /**
     * Determines whether the current player is in a "winning wave" — i.e. allowed
     * to catch fish. This is the core RTP gating mechanism.
     *
     * Returns false if the player has won MORE than (total_bet + BetWinDiffLimit),
     * meaning they are too far ahead of their expected loss and should be throttled.
     *
     * @param {Player} player
     * @returns {boolean} - true = player can win fish, false = player should miss
     */
    isWinningWave(player)
    {
        if(!player.getBetWinCondition())
            return false; // Player has won too much — suppress wins until they bet more
        return true;
    }
}

module.exports = {RedisBridge};