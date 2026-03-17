const { Common } = require('./Common');
const mysql_tools = require('./mysql_tools');
const { in_array, getRandomIntInclusive } = require('./utils.js');
var dbconn = require('./DBConn').dbconn;
var getRandomInt = require('./utils.js').getRandomInt;
var sendWSMessage = require('./Network').sendWSMessage;
class Player {
    constructor(userId, balance, isDebug) {
        this.gameName = "";
        this.userId = userId;
        this.balance = balance;
        this.seatId = -1;
        this.allowFish = false;
        this.bet_value = 5;
        this.bet_amount = 5;
        this.bet_win = 0;
        this.auto = false;
        this.weapon = 0;
        this.roomType = 0;   //0,1,2
        this.gameType = 'gd'    //gd,gf
        this.shop_id = 0;
        this.fireStormMoreTime = 0;
        this.isFireStorm = false;
        this.fireStormTotalWin = 0;
        this.mul = 1;
        this.multiBombSkillSet = null;
        this.syncTime = new Date();
        this.gameBetRange = [5, 10, 15, 20, 25, 30, 35, 40, 45, 50, 60, 70, 80, 90, 100, 150, 200, 250, 300, 350, 400, 450, 500];
        this.pingTime = new Date().getTime();
        redisBridge.setLastBalance(this);
        this.lastSkillDate = new Date();
        this.lastSkillDate.setTime(new Date().getTime() - 61 * 1000);
        this.lastCrabDate = new Date();
        this.lastGroupFishDate = new Date();
        this.isDebug = isDebug;        
        this.initialized = false;
        this.dbId = 0;
        this.isInit = false;
        this.coinrate = 100;
        this.bullet = {};
        redisBridge.getBetWin(this);
        this.BetWinDiffLimit = 10000;    
        this.winFish = [];
        this.hit = 0;
    }

    getBetWinCondition()
    {
        if(this.total_win < this.total_bet + this.BetWinDiffLimit)
            return true;
        else
            return false;
    }

    sleep(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    async bossNormalProcess(hitedFish,gameData)
    {
        // this.sendCurPlayer();
        var hitedReply = [];
        //hitedFish.odds = getRandomInt(300,500);
        hitedReply.push({
            uid: hitedFish.id,
            score: hitedFish.odds * this.bet_value * this.coinrate/10,
            rate:hitedFish.odds,
            ext:hitedFish.ext,
            fish_id: 0
        });
        this.room.fishOut(hitedFish.id);
        this.room.resetHit(hitedFish, this, hitedFish.odds * this.bet_value);
        this.balance += hitedFish.odds * this.bet_value;
        await this.updateBalance();
        await this.updateBankDirect(0,hitedFish.odds* this.bet_value);
        if (hitedReply.length > 0)
        {
            var bulletId = gameData.message.bulletid;
            if (bulletId == undefined)
            {
                bulletId = gameData.message.fblist[0].bulletid;
            }
            var hitedResponse = {
                message: {
                    bulletid: (this.seatId + 1) + '_' + bulletId,
                    pos: this.seatId + 1,
                    fishes: hitedReply,
                    // use_rate: hitedFish.odds * this.bet_value
                },
                succ: true,
                errinfo: 'ok',
                type: 'hitsprites'
            };
            sendWSMessage(this.socket,hitedResponse);
            this.sendToOtherUsers(hitedResponse);
            let self = this;
            setTimeout(function(){self.sendCurPlayer();},9);
        }
    }

    sendCurPlayer()
    {
        var response = {
            message:{
                type: 3,
                'player':{
                    uid: this.userId,
                    ws: 0,
                    pos: this.seatId + 1,
                    gunid: this.weapon,
                    gunnum: 0,
                    reward_rate: this.bet_value * this.coinrate/10,
                    score: this.balance * this.coinrate/10,
                    gold: (this.balance * this.coinrate/10) + this.bet_value * this.coinrate/10,
                    alg_diff: 0,
                    utype: 0,
                    is_lock: 0,
                    target_id: 0
                }
            },
            succ: true,
            errinfo: 'ok',
            type: 'broadplayer'
        };
        sendWSMessage(this.socket,response);
        this.sendToOtherUsers(response);
    }

    sendBalance(){
        var response = {
            message:{
                money: this.balance * this.coinrate/10
            },
            succ: true,
            errinfo: 'ok',
            type: 'userinfo'
        };
        sendWSMessage(this.socket,response);
        this.sendToOtherUsers(response);
    }

    async sendUpdateCredit()
    {
        var response = {
            data: {
                entries: this.balance,
                winnings: 0,
                timestamp: parseInt(new Date().getTime()/1000)
            },
            cmd: 'current_credit',
            sys: 'fish_player'
        };
        sendWSMessage(this.socket, response);
    }

    async noWins(gameData, targetFishIds) {
        var weaponReply = {
            cmd: gameData.cmd,
            sys: gameData.sys,
            data: {
                betRange: this.gameBetRange,
                hit_fish_id_list: [],
                result: 0,
                timestamp: parseInt(new Date().getTime() / 1000),
                puzzle_ack: {}
            }
        };
        weaponReply.data[1] = gameData.data[1]; //id
        weaponReply.data[9] = gameData.data[9]; //bet
        weaponReply.data[17] = false; //feature
        weaponReply.data[2] = this.seatId; //seat
        weaponReply.data[3] = this.balance; //credits                
        weaponReply.data[8] = {};
        weaponReply.sn = gameData.sn;
        weaponReply.data.hit_fish_id_list = targetFishIds;
        weaponReply.data.player_info = gameData.data.player_info;
        // console.log("weapon response w2 not win: " + JSON.stringify(weaponReply));
        sendWSMessage(this.socket, weaponReply);
        this.sendToOtherUsers(weaponReply);
    }

    async noWin(gameData, targetFishId) {
        var weaponReply = {
            cmd: gameData.cmd,
            sys: gameData.sys,
            data: {
                betRange: this.gameBetRange,
                hit_fish_id_list: [],
                result: 0,
                timestamp: parseInt(new Date().getTime() / 1000),
                puzzle_ack: {}
            }
        };
        weaponReply.data[1] = gameData.data[1]; //id
        weaponReply.data[9] = gameData.data[9]; //bet
        weaponReply.data[17] = false; //feature
        weaponReply.data[2] = this.seatId; //seat
        weaponReply.data[3] = this.balance; //credits                
        weaponReply.data[8] = {};
        weaponReply.data[6] = targetFishId;
        weaponReply.sn = gameData.sn;
        weaponReply.data.hit_fish_id_list.push(targetFishId);
        weaponReply.data.player_info = gameData.data.player_info;
        // console.log("weapon response w2 not win: " + JSON.stringify(weaponReply));
        sendWSMessage(this.socket, weaponReply);
        this.sendToOtherUsers(weaponReply);
    }

    /**
     * process boss fishes
     * @param {FishInfo structure} fishInfo 
     */
    async bossFishProcess(fishInfo, gameData)
    {
        var response = null     
        
        if(this.room.fishGameType == 'TD')
        {
            if (fishInfo.fishType == 78 || fishInfo.fishType == 88 || fishInfo.fishType == 29)
            {
                if(fishInfo.fishType == 29)
                {
                    //treasure crab is already processed in the Arcade.js
                }
                else if(fishInfo.fishType == 78)
                {
                    response = {
                        cmd: 'sk_25',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            seat: this.seatId,
                            id: fishInfo.id,
                            crab: fishInfo.id,
                            multi: []
                        }
                    };
                    
                    for (var i = 1; i <= this.multiBombSkillSet.bombCnt; i++)
                        response.data.multi.push(i);
                    this.mul = this.multiBombSkillSet.bombCnt;
                    this.multiBombSkillSet.skillName = 'sk_26';        
                    this.room.activeSkill = 'sk_26';
                    this.delayMsg(13000, { output: 'sk_26', id: fishInfo.id });
                }
                else
                {
                    this.multiBombSkillSet.big_bomb_times_target = 2;
                    var smallLimit = parseInt(this.multiBombSkillSet.expectedOdds * 0.3);
                    this.multiBombSkillSet.small_bomb_times_target = getRandomInt(4, 6);
                    this.multiBombSkillSet.total_win = 0;
                    this.multiBombSkillSet.bomb_exploded = 0;
                    this.multiBombSkillSet.small_bomb_times_cnt = this.multiBombSkillSet.small_bomb_times_target;
                    this.multiBombSkillSet.big_bomb_times_cnt = this.multiBombSkillSet.big_bomb_times_target;            
                    this.multiBombSkillSet.small_bomb_limit = parseInt(smallLimit / this.multiBombSkillSet.small_bomb_times_target);
                    this.multiBombSkillSet.big_bomb_limit = parseInt((this.multiBombSkillSet.expectedOdds - smallLimit) / this.multiBombSkillSet.big_bomb_times_target);        
                    this.multiBombSkillSet.id = fishInfo.id;
                    
                    response = {
                        cmd: 'sk_RedDragon_bomb',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            id: fishInfo.id,
                            seat: this.seatId,
                            big_bomb_times_target: this.multiBombSkillSet.big_bomb_times_target,
                            small_bomb_times_target: this.multiBombSkillSet.small_bomb_times_target,                    
                        }
                    }
                    this.room.activeSkill = 'sk_RedDragon_bomb';
                    this.delayMsg(8000, {output: 'sk_RedDragon_bomb_range', id: fishInfo.id});
                }
            }
            else
            {
                this.bossFishAsNormalFish(fishInfo, gameData);
            }   
        }
        else
        {
            if (fishInfo.fishType == 20) //ancient crocodile
            {
                if (this.multiBombSkillSet.bombCnt > 1) {
                    response = {
                        cmd: 'sk_kl_bomb',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            id: fishInfo.id,
                            bomb_fish_id: fishInfo.id,
                            seat: this.seatId
                        }
                    }
                    this.multiBombSkillSet.skillName = 'sk_kl_bomb_range';
                    this.multiBombSkillSet.stepOdd = this.multiBombSkillSet.expectedOdds / this.multiBombSkillSet.bombCnt;
                    this.multiBombSkillSet.total_win = 0;
                    this.room.activeSkill = 'sk_kl_bomb_range';
                }
                else {
                    this.bossFishAsNormalFish(fishInfo, gameData);
                }
            }
            else if (fishInfo.fishType == 24)
            {
                response = {
                    cmd: 'sk_25_kp',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        id: fishInfo.id,
                        multi: [],
                        seat: this.seatId
                    }
                }
                var mul = getRandomInt(3,5);
                for(var i = 0; i <= mul; i++)
                {
                    response.data.multi.push(i);
                }
                this.multiBombSkillSet.skillName = 'sk_25_kp';
                this.multiBombSkillSet.bombCnt = mul;
                this.multiBombSkillSet.total_win = 0;
                this.delayMsg(3000, {output: 'sk_26_kp', id: fishInfo.id});
            }
            else if (fishInfo.fishType == 25)
            {
                if (this.multiBombSkillSet.bombCnt > 1) {
                    response = {
                        cmd: 'sk_km_bomb',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            crab: fishInfo.id,
                            id: fishInfo.id,
                            bomb_fish_id: fishInfo.id,
                            seat: this.seatId
                        }
                    }
                    this.multiBombSkillSet.skillName = 'sk_km_bomb';
                    this.multiBombSkillSet.explodeCount = 0;
                    this.multiBombSkillSet.stepOdd = this.multiBombSkillSet.expectedOdds / this.multiBombSkillSet.bombCnt;
                }
                else {
                    this.bossFishAsNormalFish(fishInfo, gameData);
                }
            }
            else if (fishInfo.fishType == 35) {
                var response = null;
                this.room.darknessMonsterKillTime = new Date();
                if (this.multiBombSkillSet.bombCnt > 1) {
                    response = {
                        cmd: 'sk_af_bomb',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            crab: fishInfo.id,
                            id: fishInfo.id,
                            bomb_fish_id: fishInfo.id,
                            seat: this.seatId
                        }
                    }
                    this.multiBombSkillSet.skillName = 'sk_af_bomb';
                    this.multiBombSkillSet.total_win = 0;
                    this.multiBombSkillSet.oddsPerBomb = this.multiBombSkillSet.expectedOdds / this.multiBombSkillSet.bombCnt;
                    this.room.activeSkill = 'sk_af_bomb_range';
                }
                else {
                    this.bossFishAsNormalFish(fishInfo, gameData);
                }
            }
            else if (fishInfo.fishType > 61 && fishInfo.fishType <= 66) {
                if (this.multiBombSkillSet.bombCnt > 1) {
                    response = {
                        cmd: 'sk_25_kt',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            id: fishInfo.id,
                            seat: this.seatId,
                            multi: []
                        }
                    }
                    for (var i = 0; i < this.mul; i++)
                        response.data.multi.push(i);
                    this.delayMsg(11700, { output: 'sk_26_kt', id: fishInfo.id });
                }
                else
                    this.bossFishAsNormalFish(fishInfo, gameData);
            }
            else if (fishInfo.fishType == 78) {
                response = {
                    cmd: 'sk_25',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        seat: this.seatId,
                        id: fishInfo.id,
                        crab: fishInfo.id,
                        multi: []
                    }
                };
                
                for (var i = 1; i <= this.multiBombSkillSet.bombCnt; i++)
                    response.data.multi.push(i);
                this.mul = this.multiBombSkillSet.bombCnt;
                this.multiBombSkillSet.skillName = 'sk_26';                        
                this.delayMsg(13000, { output: 'sk_26', id: fishInfo.id });
            }
            else if(fishInfo.fishType == 80)
            {
                if (this.multiBombSkillSet.bombCnt > 1) {
                    response = {
                        cmd: 'sk_kp_ph',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            crab: fishInfo.id,
                            id: fishInfo.id,
                            bomb_fish_id: fishInfo.id,
                            seat: this.seatId
                        }
                    }
                    this.delayMsg(5000, { output: 'sk_kp_ph_explore', id: 0 });
                }
                else {
                    this.bossFishAsNormalFish(fishInfo, gameData);
                }
            }
            else if (fishInfo.fishType == 81) //Frenzied fury
            {
                this.assignBigSmallBombCount();
                this.multiBombSkillSet.id = fishInfo.id;
                response = {
                    cmd: 'sk_buffalo_bomb',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        id: fishInfo.id,
                        seat: this.seatId,
                        big_bomb_times_target: this.multiBombSkillSet.big_bomb_times_target,
                        small_bomb_times_target: this.multiBombSkillSet.small_bomb_times_target,
                    }
                }
                this.room.activeSkill = 'sk_buffalo_bomb';

                this.delayMsg(8000, { output: 'sk_buffalo_bomb_range', id: fishInfo.id });
            }
            else if(fishInfo.fishType == 82) 
            {
                this.assignBigSmallBombCount();
                var cmd = '';
                if(this.gameName === 'AladdinAdventure')
                    cmd = 'sk_RockSkeleton_bomb';
                else if(this.gameName === 'FishHunterKingKong')
                    cmd = 'sk_KingKong_bomb';
                else if(this.gameName === 'FishHunterGhost')
                    cmd = 'sk_RockSkeleton_bomb';
                else if(this.gameName === 'FishHunterLuckyShamrock')
                    cmd = 'sk_lucky_shamrock_bomb';
                this.multiBombSkillSet.id = fishInfo.id;            
                response = {
                    cmd: cmd,
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        id: fishInfo.id,
                        seat: this.seatId,
                        big_bomb_times_target: this.multiBombSkillSet.big_bomb_times_target,
                        small_bomb_times_target: this.multiBombSkillSet.small_bomb_times_target,                    
                    }
                }
                this.room.activeSkill = cmd;

                this.delayMsg(8000, {output: cmd + '_range', id: fishInfo.id});
            }
            else if (fishInfo.fishType == 83) //aladdin
            {
                var target_odds = this.multiBombSkillSet.expectedOdds
                var current_odds = fishInfo.odds;
                var times = Math.floor(target_odds / current_odds);
                if (times > 5)
                    times = 5;
                if(times == 0)
                    times = 1;
                var awaken_wheel = [];
                var awaken_index = -1;
                target_odds = current_odds * times;
                var k = 2;
                for (var i = 0; i <= 31; i++) {
                    awaken_wheel[i] = k;
                    k++;
                    if (k == 6)
                        k = 2;
                    if (awaken_wheel[i] == times)
                        awaken_index = i;
                }

                // awaken_index = 0;
                // times = 1;
                // target_odds = current_odds;

                var win = this.bet_value * target_odds;
                this.balance += win;
                await this.updateBalance();
                await this.updateBankDirect(0, win);
                response = {
                    sys: 'skill',
                    cmd: 'sk_Vampire',
                    data: {
                        timestamp: parseInt(new Date().getTime() / 1000),
                        odds: target_odds,
                        seat: this.seatId,
                        wheel_stop_index: awaken_index,
                        awaken_wheel: awaken_wheel,
                        Vampire_odds: current_odds,
                        awaken_multiple: times,
                        id: fishInfo.id,
                        bet: this.bet_value,
                        win: win
                    }
                }
                this.room.accumulatedOdd = 100;
                this.room.accumulatingFishDeadTime = new Date().getTime();
            }
            else if(fishInfo.fishType == 84)
            {
                var smallTimes = getRandomInt(2,5);
                var bigTimes = getRandomInt(1,3);
                var multiple = getRandomInt(1,4);
                var awaken_prize = [];
                var target_odds = fishInfo.odds;
                for(var i = 0; i < smallTimes; i++)
                {
                    var tempOdds = getRandomInt(1,6);
                    awaken_prize.push({"type":"small","value":[tempOdds]});
                    target_odds += tempOdds;
                } 
                for(var i = 0; i < bigTimes; i++)
                {
                    var tempOdds = getRandomInt(10,12);
                    awaken_prize.push({"type":"big","value":[tempOdds]});
                    target_odds += tempOdds;
                }

                var multiple = parseInt(this.multiBombSkillSet.expectedOdds / target_odds);               
                
                if (multiple > 1)
                    awaken_prize.push({"type":"multiple","value":[multiple - 1]});
                target_odds *= multiple;

                var win = this.bet_value * target_odds;
                this.balance += win;
                await this.updateBalance();
                await this.updateBankDirect(0, win);
                response = {
                    sys: 'skill',
                    cmd: 'sk_LuckyBuddha',
                    data:{
                        timestamp: parseInt(new Date().getTime()/1000),
                        odds: target_odds,
                        seat: this.seatId,
                        awaken_prize: awaken_prize,
                        LuckyBuddha_odds: fishInfo.odds,
                        id: fishInfo.id,
                        bet: this.bet_value,
                        win: win
                    }
                }
                this.room.activeSkill = 'sk_LuckyBuddha';
                this.room.accumulatedOdd = 100;
                this.room.accumulatingFishDeadTime = new Date().getTime();      
            }
            else if(fishInfo.fishType == 85)
            {
                this.assignBigSmallBombCount();
                this.multiBombSkillSet.id = fishInfo.id;
                response = {
                    cmd: 'sk_TurtleBoss_bomb',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        id: fishInfo.id,
                        seat: this.seatId,
                        big_bomb_times_target: this.multiBombSkillSet.big_bomb_times_target,
                        small_bomb_times_target: this.multiBombSkillSet.small_bomb_times_target,                    
                    }
                }
                this.room.activeSkill = 'sk_TurtleBoss_bomb';

                this.delayMsg(8000, {output: 'sk_TurtleBoss_bomb_range', id: fishInfo.id});
            }
            else if (fishInfo.fishType == 86)
            {
                var targetOdds = this.multiBombSkillSet.expectedOdds;
                var multiple = getRandomInt(2, 5);
                targetOdds = parseInt(targetOdds/multiple) * multiple;
                var unitOdds = parseInt(targetOdds/multiple);
                var oriUnitOdds = unitOdds;
                var awaken_prize = [];
                var win = oriUnitOdds * this.multiBombSkillSet.bet_value * multiple;
                var tempTimes = getRandomInt(1, 10) * 10;
                while(unitOdds - tempTimes > 0)
                {
                    awaken_prize.push(tempTimes);
                    unitOdds -= tempTimes;
                    var tempTimes = getRandomInt(60,110);
                }
                awaken_prize.push(unitOdds);

                response = {
                    cmd: 'sk_LionDance',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        id: fishInfo.id,
                        seat: this.seatId,
                        awaken_prize:awaken_prize,
                        win: win,
                        multiple:multiple
                    }
                }
                this.room.activeSkill = 'sk_LionDance';
                this.balance += win;
                await this.updateBankDirect(0, win);
                await this.updateBalance();            
            }
            else if(fishInfo.fishType == 92)
            {
                var stage1_prize = 0;
                var stage2_prize = 0;
                var odds = 0;
                
                if (this.multiBombSkillSet.bombCnt > 1) {
                    
                    stage1_prize = Math.floor(this.multiBombSkillSet.expectedOdds * 0.3);
                    stage2_prize = this.multiBombSkillSet.expectedOdds  - stage1_prize;
                }
                else
                {
                    stage1_prize = this.multiBombSkillSet.expectedOdds;
                }
                
                var stage1_prize_list = [];
                var stage2_prize_list = [];
                while(stage1_prize > 0)
                {
                    var step = 5 * getRandomInt(6, 12);
                    step = step < stage1_prize ? step: stage1_prize;
                    if(step < 30)
                        stage1_prize_list[0] += step;
                    else
                        stage1_prize_list.push(step);
                    stage1_prize -= step;
                    odds += step;
                }

                while(stage2_prize > 0)
                {
                    var step = 5 * getRandomInt(15, 40);
                    step = step < stage2_prize ? step : stage2_prize;
                    if(step < 75)
                    {
                        stage1_prize_list[0] += step;
                    }
                    else if(step >= 75 && step < 130)
                    {
                        stage2_prize_list.push({type: 0, odds: step});
                    }
                    else if(step >= 130 && step < 150)
                    {
                        stage2_prize_list.push({type: 1, odds: step});
                    }
                    else 
                    {
                        stage2_prize_list.push({type: 2, odds: step});
                    }
                    odds += step;
                    stage2_prize -= step;
                }
                var base_odds = stage1_prize_list[0];
                stage1_prize_list.shift();
                var win = odds * this.multiBombSkillSet.bet_value;
                this.balance += win;
                await this.updateBankDirect(0, win);
                await this.updateBalance();
                response = {
                    cmd: 'sk_LuckyCat',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        id: fishInfo.id,
                        seat: this.seatId,
                        stage2_prize_list: stage2_prize_list,
                        stage1_prize_list: stage1_prize_list,
                        win: win,
                        timestamp: parseInt(new Date().getTime() / 1000),
                        base_odds: base_odds,
                        odds: odds,                        
                    }
                }
            }
            else if(fishInfo.fishType == 93)
            {
                var smallTimes = getRandomInt(2,5);
                var bigTimes = getRandomInt(1,3);
                var multiple = getRandomInt(1,4);
                var awaken_prize = [];
                var target_odds = fishInfo.odds;
                for(var i = 0; i < smallTimes; i++)
                {
                    var tempOdds = getRandomInt(1,6);
                    awaken_prize.push({"type":"small","value":[tempOdds]});
                    target_odds += tempOdds;
                } 
                for(var i = 0; i < bigTimes; i++)
                {
                    var tempOdds = getRandomInt(10,12);
                    awaken_prize.push({"type":"big","value":[tempOdds]});
                    target_odds += tempOdds;
                }

                var maxMultiplier = parseInt(this.multiBombSkillSet.expectedOdds / target_odds);
                var multiple = 1;
                if(maxMultiplier < 2)
                    multiple = 1;
                else
                {
                    if(getRandomInt(0, 100) < 60)
                        multiple = 2;
                    else
                    {
                        if(maxMultiplier > 2)
                        {
                            multiple = 3;
                        }
                    }
                }
                
                if (multiple > 1)
                    awaken_prize.push({"type":"multiple","value":[multiple - 1]});
                target_odds *= multiple;

                var win = this.multiBombSkillSet.bet_value * target_odds;
                this.balance += win;
                await this.updateBalance();
                await this.updateBankDirect(0, win);
                response = {
                    sys: 'skill',
                    cmd: 'sk_CircusClown',
                    data:{
                        timestamp: parseInt(new Date().getTime()/1000),
                        odds: target_odds,
                        seat: this.seatId,
                        awaken_prize: awaken_prize,
                        CircusClown_odds: fishInfo.odds,
                        id: fishInfo.id,
                        bet: this.bet_value,
                        win: win
                    }
                }
                this.room.activeSkill = 'sk_CircusClown';
                this.room.accumulatedOdd = 150;      
                this.room.accumulatingFishDeadTime = new Date().getTime();  
            }
            else if(fishInfo.fishType == 94)
            {
                this.assignBigSmallBombCount();
                this.multiBombSkillSet.id = fishInfo.id;
                response = {
                    cmd: 'sk_CircusPig_bomb',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        id: fishInfo.id,
                        seat: this.seatId,
                        big_bomb_times_target: this.multiBombSkillSet.big_bomb_times_target,
                        small_bomb_times_target: this.multiBombSkillSet.small_bomb_times_target,
                    }
                }
                this.room.activeSkill = 'sk_CircusPig_bomb';
            }
            else if(fishInfo.fishType == 95)
            {
                var prize_list = [];
                var totalOdds = this.multiBombSkillSet.expectedOdds;
                while(totalOdds > 20)
                {
                    var poss = getRandomInt(0, 100);
                    var odd = 2;
                    var type = 0;
                    if(poss < 40)
                    {
                        odd = 2;
                        type = 0;
                    }
                    else if(poss < 70)
                    {
                        odd = 3;
                        type = 1;
                    }
                    else
                    {
                        odd =  4;
                        type = 2;
                    }
                    
                    var cnt = getRandomInt(20, 80);
                    if(cnt * odd > totalOdds)
                        cnt = Math.floor(totalOdds / odd);
                    prize_list.push({
                        cnt: cnt,
                        type: type,
                        odds: odd
                    })
                    totalOdds -= cnt * odd;
                }

                var sum = 0;
                for(var i = 0; i < prize_list.length; i++)
                {
                    sum += prize_list[i].cnt * prize_list[i].odds;
                }
                var win = sum * this.multiBombSkillSet.bet_value;
                var response = {
                    cmd : 'sk_CircusTiger',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        id: fishInfo.id,
                        odds: sum,
                        prize_list: prize_list,
                        seat: this.seatId,
                        timestamp: parseInt(new Date().getTime()/1000),
                        win: win
                    }
                };
                this.balance += win;
                await this.updateBalance();
                await this.updateBankDirect(0, win);
            }
            else if(fishInfo.fishType == 111)
            {
                //king of crab eternal squid
                var info = this.room.bulletCounter.squidInfo;
                
                var isKilling = false;
                var totalOdds = this.multiBombSkillSet.expectedOdds;
                if(totalOdds > 300)
                {
                    isKilling = true;
                    info.bullets = 0;
                    this.room.fishOut(fishInfo.id);
                }
                
                var win_type = 'small';
                if(totalOdds >= 200)
                    win_type = 'middle';
                else if(totalOdds >= 500)
                    win_type = 'big';
                if(isKilling)
                    win_type = 'kill';

                var win = totalOdds * this.multiBombSkillSet.bet_value;
                var response = {
                    cmd: 'sk_eternal',
                    data: {
                        bet: this.bet_value,
                        fish: fishInfo.id,
                        odds: totalOdds,
                        seat: this.seatId,
                        size: info.size,
                        timestamp: parseInt(new Date().getTime()/1000),
                        win: win,
                        win_type: win_type
                    },
                    sys: 'skill'
                };
                
                this.balance += win;
                await this.updateBalance();
                await this.updateBankDirect(0, win);
            }
            else if(fishInfo.fishType == 112)
            {
                //eternal phoenix
                if (this.multiBombSkillSet.bombCnt > 1) {
                    response = {
                        cmd: 'sk_kp_ph',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            crab: fishInfo.id,
                            id: fishInfo.id,
                            bomb_fish_id: fishInfo.id,
                            seat: this.seatId
                        }
                    }
                    this.delayMsg(5000, { output: 'sk_kp_ph_explore', id: 0 });
                }
                else 
                {
                    var totalOdds = this.multiBombSkillSet.expectedOdds;
                    var win_type = 'small';
                    if(totalOdds >= 200)
                        win_type = 'middle';
                    else if(totalOdds >= 500)
                        win_type = 'big';                    

                    var win = totalOdds * this.multiBombSkillSet.bet_value;
                    var response = {
                        cmd: 'sk_eternal',
                        data: {
                            bet: this.bet_value,
                            fish: fishInfo.id,
                            odds: totalOdds,
                            old_level: old_level,
                            seat: this.seatId,
                            timestamp: parseInt(new Date().getTime()/1000),
                            win: win,
                            win_type: win_type
                        },
                        sys: 'skill'
                    };
                    
                    this.balance += win;
                    await this.updateBalance();
                    await this.updateBankDirect(0, win);
                }
            }
            else if(fishInfo.fishType == 113)
            {
                //king of crab lobster
                var info = this.room.bulletCounter.edragonInfo;
                var level = info.levels[info.level_index];                
                
                var old_level = info.cur_level;
                if(info.bullets >= info.level_bullets[level])
                {
                    info.level_index++;
                    if(info.level_index >= info.levels.length)
                        info.level_index = 0;
                    info.bullets = 0;
                    info.cur_level = info.levels[info.level_index];
                    this.room.bulletCounter.fishData[fishInfo.fishType].level = info.cur_level;
                }
                
                if(old_level > 0)
                {
                    this.multiBombSkillSet.big_bomb_times_target = 2;
                    var smallLimit = parseInt(this.multiBombSkillSet.expectedOdds * 0.3);
                    this.multiBombSkillSet.small_bomb_times_target = getRandomInt(4, 6);
                    this.multiBombSkillSet.total_win = 0;
                    this.multiBombSkillSet.bomb_exploded = 0;
                    this.multiBombSkillSet.small_bomb_times_cnt = this.multiBombSkillSet.small_bomb_times_target;
                    this.multiBombSkillSet.big_bomb_times_cnt = this.multiBombSkillSet.big_bomb_times_target;            
                    this.multiBombSkillSet.small_bomb_limit = parseInt(smallLimit / this.multiBombSkillSet.small_bomb_times_target);
                    this.multiBombSkillSet.big_bomb_limit = parseInt((this.multiBombSkillSet.expectedOdds - smallLimit) / this.multiBombSkillSet.big_bomb_times_target);        
                    this.multiBombSkillSet.id = fishInfo.id;
                    
                    response = {
                        cmd: 'sk_RedDragon_bomb',
                        sys: 'skill',
                        data: {
                            bet: this.bet_value,
                            id: fishInfo.id,
                            seat: this.seatId,
                            big_bomb_times_target: this.multiBombSkillSet.big_bomb_times_target,
                            small_bomb_times_target: this.multiBombSkillSet.small_bomb_times_target,                    
                        }
                    }
                    this.room.activeSkill = 'sk_RedDragon_bomb';
                    this.delayMsg(8000, {output: 'sk_RedDragon_bomb_range', id: fishInfo.id});
                    this.room.fishOut(fishInfo.id);
                }
                else
                {
                    //level 0 is eternal mode
                    var totalOdds = this.multiBombSkillSet.expectedOdds;
                    var win_type = 'middle';
                    
                    var win = totalOdds * this.multiBombSkillSet.bet_value;
                    var response = {
                        cmd: 'sk_eternal',
                        data: {
                            bet: this.bet_value,
                            fish: fishInfo.id,
                            new_level: new_level,
                            odds: totalOdds,
                            old_level: old_level,
                            seat: this.seatId,
                            size: info.size,
                            timestamp: parseInt(new Date().getTime()/1000),
                            win: win,
                            win_type: win_type
                        },
                        sys: 'skill'
                    };
                    
                    this.balance += win;
                    await this.updateBalance();
                    await this.updateBankDirect(0, win);
                }
            }
            else if(fishInfo.fishType == 114)
            {
                //king of crab lobster
                var info = this.room.bulletCounter.lobsterInfo;
                var level = info.levels[info.level_index];                
                
                var old_level = info.cur_level;
                if(info.bullets >= info.level_bullets[level])
                {
                    info.level_index++;
                    if(info.level_index >= info.levels.length)
                        info.level_index = 0;
                    info.bullets = 0;
                    info.cur_level = info.levels[info.level_index];
                    fishInfo.level = info.cur_level;
                    this.room.bulletCounter.fishData[fishInfo.fishType].level = info.cur_level;
                }
                
                var new_level = info.cur_level;
                if(new_level == 0 && old_level != 0)
                    this.room.fishOut(fishInfo.id);       
                var totalOdds = this.multiBombSkillSet.expectedOdds;
                var win_type = 'small';
                if(totalOdds >= 200)
                    win_type = 'middle';
                else if(totalOdds >= 500)
                    win_type = 'big';
                if(new_level != old_level)
                    win_type = 'kill';

                var win = totalOdds * this.multiBombSkillSet.bet_value;
                var response = {
                    cmd: 'sk_eternal',
                    data: {
                        bet: this.bet_value,
                        fish: fishInfo.id,
                        new_level: new_level,
                        odds: totalOdds,
                        old_level: old_level,
                        seat: this.seatId,
                        size: info.size,
                        timestamp: parseInt(new Date().getTime()/1000),
                        win: win,
                        win_type: win_type
                    },
                    sys: 'skill'
                };
                
                this.balance += win;
                await this.updateBalance();
                await this.updateBankDirect(0, win);
            }
            else if(fishInfo.fishType == 115)
            {
                //king of crab  slot crab
                var info = this.room.bulletCounter.slotCrabInfo;
                var level = info.levels[info.level_index];                
                
                if(info.bullets >= info.level_bullets[level])
                {
                    info.level_index++;
                    if(info.level_index >= info.levels.length)
                        info.level_index = 0;
                    info.bullets = 0;
                    info.cur_level = info.levels[info.level_index];
                    fishInfo.level = info.cur_level;
                    this.room.bulletCounter.fishData[fishInfo.fishType].level = info.cur_level;
                }

                var totalOdds = this.multiBombSkillSet.expectedOdds;
                var symbols = [];
                var win_type = 0;
                switch(totalOdds)
                {
                    case 30:
                        var reelGenerated = false;
                        while(!reelGenerated)
                        {
                            var sym1 = getRandomIntInclusive(0, 2);
                            var sym2 = getRandomIntInclusive(0, 2);
                            var sym3 = getRandomIntInclusive(0, 2);
                            if(sym1 == sym2 && sym2 == sym3)
                                reelGenerated = false;
                            else
                                reelGenerated = true;
                            symbols = [sym1, sym2, sym3];
                        }        
                        win_type = 0;                
                        break;
                    case 50:
                        var sym1 = getRandomIntInclusive(3, 4);
                        var sym2 = getRandomIntInclusive(0, 2);
                        var sym3 = getRandomIntInclusive(0, 2);
                        symbols = [sym1, sym2, sym3];
                        win_type = 1;
                        break;
                    case 80:
                        var sym1 = getRandomIntInclusive(3, 4);
                        var sym2 = getRandomIntInclusive(3, 4);
                        var sym3 = getRandomIntInclusive(0, 2);
                        symbols = [sym1, sym2, sym3];
                        win_type = 2;
                        break;
                    case 120:
                        symbols = [0, 0, 0];
                        win_type = 3;
                        break;
                    case 200:
                        symbols = [1, 1, 1];
                        win_type = 4;
                        break;
                    case 300:
                        symbols = [2, 2, 2];
                        win_type = 5;
                        break;
                    case 500:
                        var sym1 = getRandomIntInclusive(3, 4);
                        var sym2 = getRandomIntInclusive(3, 4);
                        var sym3 = getRandomIntInclusive(3, 4);
                        symbols = [sym1, sym2, sym3];
                        win_type = 6;
                        break;
                    case 1000:
                        symbols = [3, 3, 3];
                        win_type = 7;
                        break;
                    case 2000:
                        symbols = [4, 4, 4];
                        win_type = 8;
                        break;
                }
                
                var win = totalOdds * this.multiBombSkillSet.bet_value;
                var response = {
                    cmd: 'sk_king_of_crab',
                    data: {
                        bet: this.multiBombSkillSet.bet_value,
                        fish: fishInfo.id,
                        odds: totalOdds,
                        seat: this.seatId,
                        symbol: symbols,
                        timestamp: parseInt(new Date().getTime()/1000),
                        win: win,
                        win_type: win_type
                    },
                    sys: 'skill'
                };
                
                this.balance += win;
                await this.updateBalance();
                await this.updateBankDirect(0, win);
            }
            else if(fishInfo.fishType == 116)
            {
                var odds = fishInfo.odds;
                var winVal = this.multiBombSkillSet.bet_value * odds;            
                await this.updateBankDirect(0, winVal);
                this.balance += winVal;
                await this.updateBalance();

                this.lastSkillDate = new Date();
                this.room.accumulatedOdd = 0;
                this.room.godWealthOdd = 300;
                var weaponReply = { data: {} }
                weaponReply.data[8] = {};
                weaponReply.cmd = gameData.cmd;
                weaponReply.sys = gameData.sys;
                weaponReply.sn = gameData.sn;
                weaponReply.data[1] = gameData.data[1];
                weaponReply.data[9] = gameData.data[9];
                
                weaponReply.data[17] = false; //feature
                weaponReply.data[2] = this.seatId;
                weaponReply.data.timestamp = new Date().getTime();
                weaponReply.data[3] = this.balance;
                
                var fishDeadInfo = {};
                fishDeadInfo[10] = odds;
                fishDeadInfo[11] = winVal;
                fishDeadInfo[13] = {};
                fishDeadInfo[13].freeze_amount = 0;
                fishDeadInfo[13].summon_amount = 0;
                weaponReply.data[8][fishInfo.id] = fishDeadInfo;
                weaponReply.data.hit_fish_id_list = [fishInfo.id];
                weaponReply.data.betRange = this.gameBetRange;
                weaponReply.data.result = 0;
                weaponReply.data.puzzle_ack = {};
                this.room.sendToAllUsers(weaponReply);

                this.room.accumulatedOdd = 0;
                this.room.accumulatingFishDeadTime = new Date().getTime();
            }
        }
        this.room.sendToAllUsers(response);
        this.room.resetHit(fishInfo, this, this.multiBombSkillSet.expectedOdds * this.bet_value);
    }

    /**
     * process boss fish as normal fish 
     * @param {*Boss fish info} fishInfo 
     * @param {*Socket request data} gameData 
     */
    async bossFishAsNormalFish(fishInfo, gameData) {
        var odds = this.multiBombSkillSet.expectedOdds;
        var winVal = odds * this.multiBombSkillSet.bet_value;
        await this.updateBankDirect(0, winVal);
        this.balance += winVal;
        await this.updateBalance();
        var weaponReply = { data: {} }
        weaponReply.data[8] = {};
        weaponReply.cmd = gameData.cmd;
        weaponReply.sys = gameData.sys;
        weaponReply.sn = gameData.sn;
        weaponReply.data[1] = gameData.data[1]; //id
        weaponReply.data[9] = gameData.data[9]; //bet

        weaponReply.data[17] = false; //feature
        weaponReply.data[2] = this.seatId;
        weaponReply.data.timestamp = new Date().getTime();
        weaponReply.data[3] = this.balance;
        weaponReply.data[10] = odds;
        var fishDeadInfo = {};
        fishDeadInfo[10] = odds;
        fishDeadInfo[11] = winVal;
        fishDeadInfo[13] = {};
        fishDeadInfo[13].freeze_amount = 0;
        fishDeadInfo[13].summon_amount = 0;
        weaponReply.data[8][fishInfo.id] = fishDeadInfo;
        weaponReply.data.hit_fish_id_list = [fishInfo.id];
        weaponReply.data.betRange = this.gameBetRange;
        weaponReply.data.result = 0;
        weaponReply.data.puzzle_ack = {};
        this.room.sendToAllUsers(weaponReply);
    }

    async sendToOtherUsers(data) {
        var room = this.room;
        if(room == undefined || room.clients == undefined)
            return;

        var players = [];
        this.room.clients.forEach(element => {
            players.push({
                entries: element.balance,
                equip_avatar: "",
                equip_avatar_frame: "",
                nick_name: "",
                seat: element.seatId,
                winnings: 0
            });
        });
        if((data.sys == 'skill' || data.cmd == 'w1') && data.data != undefined)
            data.data.player_info = players;
        room.clients.forEach(element => {
            if (element != this && element.initialized)
                sendWSMessage(element.socket, data);
        });
    }

    async delayMsg(delay, msg) {
        await this.sleep(delay);
        var orderedFishInfo = this.room.fishInfos.sort((a, b) => a.odds > b.odds ? 1 : -1);
        var fish_dead_dict = {};
        switch (msg.output) {
            case 'sk18':
                while(this.fireStormMoreTime > 0)
                {
                    await this.sleep(1000);
                    this.fireStormMoreTime--;
                }
                
                this.isFireStorm = false;
                this.room.isFireStorm = false;
                var total_win = this.fireStormTotalWin * this.mul;
                var fireStormEnd = {
                    cmd: 'sk18',
                    sys: 'skill',
                    data: {
                        credits: this.balance,
                        seat: this.seatId,
                        total_win: total_win
                    }
                }
                await this.updateBankDirect(0, total_win);
                this.balance += total_win;
                await this.updateBalance();
                this.fireStormMoreTime = 0;
                this.room.sendToAllUsers(fireStormEnd);
                break;
            case 'sk_flash_hammer_explore':
                if (this.skillFishContext != null && this.skillFishContext.skillName == 'sk_flash_hammer') {
                    var hammerCabBomReply = {
                        cmd: 'sk_flash_hammer_explore',
                        sys: 'skill',
                        data: {
                            timestamp: parseInt(new Date().getTime() / 1000),
                            seat: this.seatId,
                            total_win: 0,
                            fish_dead_dict: {}
                        }
                    };

                    try {
                        orderedFishInfo.forEach(temp => {
                            var win = 0;
                            if(temp != undefined && this.skillFishContext != null 
                                && this.skillFishContext.expectedOdds > temp.odds
                                && (temp.fishTypeGroup == 'normal' || temp.fishType == 80 || temp.fishType == 25)
                                && (temp.odds <= 20 || (temp.fishType == 80 && temp.odds <= 200) || (temp.fishType == 25 && temp.odds <= 200) ))
                            {
                                win = temp.odds * this.skillFishContext.bet_value;
                                hammerCabBomReply.data.total_win += win;
                                var tempDead = {
                                    odds: temp.odds,
                                    coin: win,
                                    fs_item_dict: {}
                                }
                                fish_dead_dict[temp.id] = tempDead;
                                this.room.fishOut(temp.id);
                                this.skillFishContext.expectedOdds -= temp.odds;
                            }
                            else if (this.skillFishContext.exceptedOdds < temp.odds) {
                                throw 'Break';
                            }
                        });
                    } catch (e) {
                        if (e !== 'Break') throw e
                    }
                    this.balance += hammerCabBomReply.data.total_win;
                    await this.updateBankDirect(0, hammerCabBomReply.data.total_win);
                    await this.updateBalance();
                    this.skillFishContext = null;
                    hammerCabBomReply.data.fish_dead_dict = fish_dead_dict;
                    this.room.sendToAllUsers(hammerCabBomReply);
                }
                break;
            case 'sk_26':
                if (this.multiBombSkillSet != null && this.multiBombSkillSet.skillName == 'sk_26') {
                    var thunderDragonBombReply = {
                        cmd: 'sk_26',
                        sys: 'skill',
                        data: {
                            timestamp: parseInt(new Date().getTime() / 1000),
                            seat: this.seatId,
                            bet: this.multiBombSkillSet.bet_value,
                            id: msg.id,
                            total_win: 0
                        }
                    }

                    orderedFishInfo.forEach(temp => {
                        var win = 0;
                        if (temp.fishTypeGroup == 'normal' && this.multiBombSkillSet.expectedOdds > temp.odds * this.multiBombSkillSet.bombCnt && temp.odds <= 15) {
                            win = temp.odds * this.multiBombSkillSet.bet_value;
                            thunderDragonBombReply.data.total_win += win;
                            var tempDead = {
                                odds: temp.odds,
                                coin: win,
                                fs_item_dict: {}
                            }
                            fish_dead_dict[temp.id] = tempDead;
                            this.room.fishOut(temp.id);
                            this.multiBombSkillSet.expectedOdds -= temp.odds * this.multiBombSkillSet.bombCnt;
                        }
                    });
                    thunderDragonBombReply.data.total_win *= this.multiBombSkillSet.bombCnt;
                    this.mul = 1;
                    this.balance += thunderDragonBombReply.data.total_win;
                    thunderDragonBombReply.data.fish_dead_dict = fish_dead_dict;
                    await this.updateBalance();
                    await this.updateBankDirect(0, thunderDragonBombReply.data.total_win);
                    thunderDragonBombReply.data.credits = this.balance;
                    // console.log("thunder dragon: " + JSON.stringify(thunderDragonBombReply));
                    this.room.sendToAllUsers(thunderDragonBombReply);
                    this.room.activeSkill = '';
                }
                break;
            case 'sk_26_kp':
                var kingCrabBombReply = {
                    cmd: 'sk_26_kp',
                    sys: 'skill',
                    data: {
                        total_win: 0,
                        seat: this.seatId,
                        bet: this.bet_value,
                        id: msg.id
                    }
                };
                orderedFishInfo.forEach(temp => {
                    var win = 0;
                    if(temp.fishTypeGroup == 'normal' && this.multiBombSkillSet.expectedOdds > temp.odds * this.multiBombSkillSet.bombCnt && temp.odds < 20)
                    {
                        win = temp.odds * this.multiBombSkillSet.bet_value;
                        kingCrabBombReply.data.total_win += win;
                        var tempDead = {
                            odds: temp.odds,
                            coin: win,
                            fs_item_dict: {}
                        };
                        
                        fish_dead_dict[temp.id] = tempDead;                            
                        this.room.fishOut(temp.id);
                        this.multiBombSkillSet.expectedOdds -= temp.odds * this.multiBombSkillSet.bombCnt;
                    }
                });
                kingCrabBombReply.data.total_win *= this.multiBombSkillSet.bombCnt;
                await this.updateBankDirect(0,kingCrabBombReply.data.total_win);
                this.balance += kingCrabBombReply.data.total_win;
                await this.updateBalance();
                kingCrabBombReply.data.credits = this.balance;
                kingCrabBombReply.data.fish_dead_dict = fish_dead_dict;
                this.room.sendToAllUsers(kingCrabBombReply);
                this.lastSkillDate.setTime(new Date().getTime() - 61 * 1000);
                break;
            case 'sk_kp_ph_explore':
                var hit = 0;
                var phoenixBombRangeReply = {
                    cmd: 'sk_kp_ph_explore',
                    sys: 'skill',
                    data: {
                        total_wins: 0,
                        seat: this.seatId,
                        bet: this.bet_value,
                        continues: true,
                        hit_count: 0
                    }
                };
                var totalOdds = this.multiBombSkillSet.expectedOdds;
                var responseCount = 0;
                while (this.multiBombSkillSet.expectedOdds > 20 && phoenixBombRangeReply.data.continues) {
                    responseCount++;
                    orderedFishInfo = this.room.fishInfos.sort((a, b) => a.odds > b.odds ? 1 : -1);
                    var step_hit = 0;
                    var target_step_hit = getRandomInt(3, 10);
                    fish_dead_dict = {};

                    if(this.multiBombSkillSet.expectedOdds > totalOdds / 4)
                    {
                        try {
                            orderedFishInfo.forEach(temp => {
                                var win = 0;
                                if (temp.fishTypeGroup == 'normal' && this.multiBombSkillSet.expectedOdds > temp.odds && temp.odds <= 15) {
                                    win = temp.odds * this.multiBombSkillSet.bet_value;
                                    phoenixBombRangeReply.data.total_wins += win;
                                    var tempDead = {
                                        odds: temp.odds,
                                        coin: win,
                                        fs_item_dict: {}
                                    };
                                    fish_dead_dict[temp.id] = tempDead;
                                    this.room.fishOut(temp.id);
                                    this.multiBombSkillSet.expectedOdds -= temp.odds;
                                    step_hit++;
                                    hit += 1;
                                    if (step_hit == target_step_hit || this.multiBombSkillSet.expectedOdds < totalOdds / 4)
                                        throw 'Break';
                                }
                            });
                        }
                        catch (e) {
                            if (e !== 'Break') throw e
                        }
                    }
                    else
                    {
                        //process 1/4 odds with last explosion
                        phoenixBombRangeReply.data.continues = false;
                        // console.log("last bullet odd: " + this.multiBombSkillSet.expectedOdds);
                        try{
                        orderedFishInfo.forEach(temp => {
                            var win = 0;
                            if (temp.fishTypeGroup == 'normal' && this.multiBombSkillSet.expectedOdds > temp.odds) {
                                win = temp.odds * this.multiBombSkillSet.bet_value;
                                phoenixBombRangeReply.data.total_wins += win;
                                var tempDead = {
                                    odds: temp.odds,
                                    coin: win,
                                    fs_item_dict: {}
                                };
                                fish_dead_dict[temp.id] = tempDead;
                                this.room.fishOut(temp.id);
                                this.multiBombSkillSet.expectedOdds -= temp.odds;                                
                                hit += 2;
                                step_hit++;
                                if(this.multiBombSkillSet.expectedOdds <= 10)
                                    throw 'Break';
                            }
                        });
                        }catch(e)
                        {
                            if (e !== 'Break') throw e
                        }
                        // console.log("last bullet fishes: " + step_hit);
                    }

                    this.mul = 1;

                    phoenixBombRangeReply.data.credits = this.balance;
                    phoenixBombRangeReply.data.fish_dead_dict = fish_dead_dict;
                    phoenixBombRangeReply.data.hit_count = hit;

                    this.room.sendToAllUsers(phoenixBombRangeReply);
                    if(responseCount % 2 == 0)
                        await this.sleep(1000);
                    else
                        await this.sleep(2000);
                }
                this.balance += phoenixBombRangeReply.data.total_wins;
                await this.updateBankDirect(0, phoenixBombRangeReply.data.total_wins);
                await this.updateBalance();
                break;
            case 'sk_26_kt':
                var kingTaCoBomb = {
                    cmd: 'sk_26_kt',
                    sys: 'skill',
                    data: {
                        total_win: 0,
                        seat: this.seatId,
                        id: msg.id
                    }
                };
                orderedFishInfo.forEach(temp => {
                    var win = 0;
                    if (temp.fishTypeGroup == 'normal' && this.multiBombSkillSet.expectedOdds > temp.odds * this.mul) {
                        win = temp.odds * this.multiBombSkillSet.bet_value;
                        kingTaCoBomb.data.total_win += win;
                        var tempDead = {
                            coin: win
                        };

                        fish_dead_dict[temp.id] = tempDead;
                        this.room.fishOut(temp.id);
                        this.multiBombSkillSet.expectedOdds -= temp.odds * this.mul;
                    }
                });

                kingTaCoBomb.data.total_win *= this.mul;
                this.mul = 1;
                this.balance += kingTaCoBomb.data.total_win;
                await this.updateBankDirect(0, kingTaCoBomb.data.total_win);
                await this.updateBalance();
                kingTaCoBomb.data.credits = this.balance;
                kingTaCoBomb.data.fish_dead_dict = fish_dead_dict;
                this.room.sendToAllUsers(kingTaCoBomb);
                break;
            case 'sk_RockSkeleton_bomb_range':
                var total_win = 0;
                var small_bomb_cnt = 0;
                var big_bomb_cnt = 0;
                while (small_bomb_cnt + big_bomb_cnt < this.multiBombSkillSet.small_bomb_times_target + this.multiBombSkillSet.big_bomb_times_target) {
                    var sleepTime = 5000;
                    if (this.multiBombSkillSet.small_bomb_times_target > small_bomb_cnt) {
                        small_bomb_cnt++;
                    }
                    else if (this.multiBombSkillSet.big_bomb_times_target > big_bomb_cnt) {
                        big_bomb_cnt++;
                    }

                    if (small_bomb_cnt == this.multiBombSkillSet.small_bomb_times_target && big_bomb_cnt == 0 && this.multiBombSkillSet.big_bomb_times_target > 0)
                        sleepTime = 17000;

                    var isContinue = true;
                    if (this.multiBombSkillSet.small_bomb_times_target == small_bomb_cnt && this.multiBombSkillSet.big_bomb_times_target == big_bomb_cnt)
                        isContinue = false;

                    if (isContinue)
                        await this.sleep(sleepTime);
                }

                await this.sleep(13000);
                this.room.activeSkill = '';
                break;
            case "sk_KingKong_bomb_range":
                var total_win = 0;
                var small_bomb_cnt = 0;
                var big_bomb_cnt = 0;
                while (small_bomb_cnt + big_bomb_cnt < this.multiBombSkillSet.small_bomb_times_target + this.multiBombSkillSet.big_bomb_times_target) {
                    var sleepTime = 5000;
                    if (this.multiBombSkillSet.small_bomb_times_target > small_bomb_cnt) {
                        small_bomb_cnt++;
                    }
                    else if (this.multiBombSkillSet.big_bomb_times_target > big_bomb_cnt) {
                        big_bomb_cnt++;
                    }

                    if (small_bomb_cnt == this.multiBombSkillSet.small_bomb_times_target && big_bomb_cnt == 0 && this.multiBombSkillSet.big_bomb_times_target > 0)
                        sleepTime = 17000;

                    var isContinue = true;
                    if (this.multiBombSkillSet.small_bomb_times_target == small_bomb_cnt && this.multiBombSkillSet.big_bomb_times_target == big_bomb_cnt)
                        isContinue = false;

                    if (isContinue)
                        await this.sleep(sleepTime);
                }

                await this.sleep(10000);
                this.room.activeSkill = '';
                break;
            case 'sk_RedDragon_bomb_range':
                var total_win = 0;
                var small_bomb_cnt = 0;
                var big_bomb_cnt = 0;
                while(small_bomb_cnt + big_bomb_cnt < this.multiBombSkillSet.small_bomb_times_target + this.multiBombSkillSet.big_bomb_times_target)
                {
                    if(this.multiBombSkillSet.small_bomb_times_target > small_bomb_cnt)
                    {
                        small_bomb_cnt++;
                    }
                    else if(this.multiBombSkillSet.big_bomb_times_target > big_bomb_cnt)
                    {
                        big_bomb_cnt++;
                    }
                    await this.sleep(5000);
                }
                await this.sleep(13*1000);
                this.room.activeSkill = '';
                this.lastSkillDate.setTime(new Date().getTime() - 61 * 1000);
                break;
        }
    }

    async removeCurrentFishPlayerInSameRoom() {
        var leaveRoom = {
            cmd: 'leave_table',
            sys: 'game',
            data: {
                seat: this.seatId
            }
        }
        this.sendToOtherUsers(leaveRoom);
    }


    skillFishProcessGF(hitedFish,expectedOdds)
    {
        this.room.fishOut(hitedFish.id);
        this.room.resetHit(hitedFish, this, expectedOdds * this.bet_value);
        if (hitedFish.fishId == 27)
        {
            var bombCnt = getRandomInt(3,5);
            var points = [];
            for(var i = 0; i < bombCnt; i++)
            {   
                points.push({
                    x: getRandomInt(0,1000),
                    y: getRandomInt(0,600)
                });
            }
            var hitedResponse = {
                message: {
                    bombs: [{
                        pos: this.seatId + 1,
                        type:hitedFish.fishId,
                        fish_id: hitedFish.id,
                        id: 1,
                        points:points,
                        born_time: new Date().getTime(),
                        dead_time: new Date().getTime() + bombCnt * 2000,
                        brate:this.coinrate,
                        bomb_cnt: bombCnt,
                        use_rate: 0,
                        num:bombCnt
                    }]
                },
                succ: true,
                errinfo: 'ok',
                type: 'increasebombs'
            };
            this.multiBombSkillSet = {
                bombCnt:bombCnt,
                totalWin:0,
                expectedOdds: expectedOdds,
                oddPerBomb: expectedOdds / bombCnt
            };
            sendWSMessage(this.socket,hitedResponse);
            this.sendToOtherUsers(hitedResponse);
        } else if (hitedFish.fishId == 28)
        {
            var nowDate = new Date().getTime();
            var hitedResponse = {
                message: {
                    bombs: [{
                        pos: this.seatId + 1,
                        type:hitedFish.fishId,
                        fish_id: hitedFish.id,
                        id: 1,
                        points:[],
                        born_time: nowDate,
                        dead_time: nowDate + 10000,
                        brate:this.coinrate,
                        bomb_cnt: 1,
                        use_rate: 0,
                        num:bombCnt
                    }]
                },
                succ: true,
                errinfo: 'ok',
                type: 'increasebombs'
            };
            this.multiBombSkillSet = {
                bombCnt:1,
                totalWin:0,
                skillName: '28_skill',
                bornTime: nowDate,
                deadTime: nowDate + 10000,
                expectedOdds: expectedOdds,
                oddPerBomb: expectedOdds
            };
            sendWSMessage(this.socket,hitedResponse);
            this.sendToOtherUsers(hitedResponse);
        } else if (hitedFish.fishId == 29)
        {
            var hitedResponse = {
                message: {
                    bombs: [{
                        pos: this.seatId + 1,
                        type:hitedFish.fishId,
                        class_id:hitedFish.fishId,
                        flag: 1,
                        fish_id: hitedFish.id,
                        id: 1,
                        points:[],
                        born_time: new Date().getTime(),
                        dead_time: new Date().getTime() + 20000,
                        brate:this.coinrate,
                        bomb_cnt: 1,
                        use_rate: 0,
                        rate: expectedOdds,
                        num:1,
                        uid: this.userId,
                    }]
                },
                succ: true,
                errinfo: 'ok',
                type: 'increasebombs'
            };
            this.multiBombSkillSet = {
                bombCnt:1,
                num: 1,
                type: hitedFish.fishId,
                fishId: hitedFish.id,
                shot_time: new Date().getTime() + 600,
                totalWin:0,
                skillName: '29_skill',
                born_time: new Date().getTime(),
                dead_time: new Date().getTime() + 20000,
                expectedRate: expectedOdds,
                oddPerBomb: expectedOdds
            };
            sendWSMessage(this.socket,hitedResponse);
            this.sendToOtherUsers(hitedResponse);
            this.sendBalance();
        } else if (hitedFish.fishId == 30)
        {
            var kv = [20,80,40,60,150,180,190,240,740];
            var index = getRandomInt(1,kv.length);
            var hitedResponse = {
                message: {
                    pos: this.seatId + 1,
                    id: index,
                    kv:[20,80,40,60,150,180,190,240,740],
                    bullet_rate: this.bet_value * this.coinrate / 10,
                    fishid:hitedFish.id,
                    score: this.bet_value * kv[index -1] * this.coinrate/10
                },
                succ: true,
                errinfo: 'ok',
                type: 'zhuanpan'
            };
            this.balance += this.bet_value * kv[index];
            this.updateBalance();
            this.updateBankDirect(0,this.bet_value * kv[index]);
            sendWSMessage(this.socket,hitedResponse);
            this.sendToOtherUsers(hitedResponse);
            return;
        } else if (hitedFish.fishId == 31)
        {
            var nowDate = new Date().getTime();
            var hitedResponse = {
                message: {
                    bombs: [{
                        pos: this.seatId + 1,
                        type:hitedFish.fishId,
                        class_id:hitedFish.fishId,
                        flag: 1,
                        fish_id: hitedFish.id,
                        id: 1,
                        points:[],
                        born_time: nowDate + 1313,
                        dead_time: nowDate + 1313 + 9778,
                        del_time: nowDate + 1313 + 12778,
                        brate:this.coinrate,
                        bomb_cnt: 1,
                        use_rate: 0,
                        rate: expectedOdds,
                        num:1,
                        uid: this.userId,
                        ext:0,
                        cond_rate:0,
                        shot_time:0,
                        tons_list:[]
                    }]
                },
                succ: true,
                errinfo: 'ok',
                type: 'increasebombs'
            };
            this.multiBombSkillSet = {
                bombCnt:1,
                num: 1,
                type: hitedFish.fishId,
                fishId: hitedFish.id,
                shot_time: 0,
                totalWin:0,
                skillName: '31_skill',
                born_time: nowDate + 1313,
                dead_time: nowDate + 1313 + 9778,
                del_time: nowDate + 1313 + 12778,
                expectedRate: expectedOdds,
                oddPerBomb: expectedOdds
            };
            sendWSMessage(this.socket,hitedResponse);
            this.sendToOtherUsers(hitedResponse);
            this.sendBalance();
        }
        this.multiBombSkillSet.bet_value = this.bet_value;
    }

    async skillFishProcess(fish, sn, expectedOdds) {
        if (this.gameType == 'gf')
        {
            this.skillFishProcessGF(fish,expectedOdds);
        }
        else if (this.gameType == 'gd')
        {
            this.room.resetHit(fish, this, expectedOdds * this.bet_value);
            var response;
            this.room.fishOut(fish.id);
            if (fish.fishType == 79) {
                response = {
                    cmd: 'sk_flash_hammer',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        seat: this.seatId,
                        id: fish.id
                    }
                };
    
                this.skillFishContext = {
                    skillName: 'sk_flash_hammer',
                    expectedOdds: expectedOdds,
                    bombCnt: 1,
                    total_win: 0,
                    bet_value: this.bet_value
                };
                var msg = {
                    output: 'sk_flash_hammer_explore',
                }
                this.delayMsg(7300, msg);
            }
            else if (fish.fishType == 22) {
                response = {
                    cmd: 'sk_electric',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        seat: this.seatId,
                        id: fish.id,
                        crab: fish.id
                    }
                };
                this.skillFishContext = {
                    skillName: 'sk_electric',
                    expectedOdds: expectedOdds,
                    bombCnt: 1,
                    total_win: 0,
                    bet_value: this.bet_value
                }
            }
            else if (fish.fishType == 23) {
                response = {
                    cmd: 'sk_drill',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        seat: this.seatId,
                        id: fish.id,
                        crab: fish.id
                    }
                };
                this.skillFishContext = {
                    skillName: 'sk_drill',
                    expectedOdds: expectedOdds,
                    totalOdds: expectedOdds,
                    bombCnt: 1,
                    total_win: 0,
                    bet_value: this.bet_value
                }
            }
            else if (fish.fishType == 34) {
                response = {
                    cmd: 'sk_bomb',
                    sys: 'skill',
                    data: {
                        bet: this.bet_value,
                        seat: this.seatId,
                        id: fish.id,
                        crab: fish.id
                    }
                };
                var bombCnt = getRandomInt(3, 5);
                this.skillFishContext = {
                    skillName: 'sk_bomb',
                    expectedOdds: expectedOdds,
                    bombCnt: bombCnt,
                    targetBombCnt: bombCnt,
                    stepOdd: expectedOdds / bombCnt,
                    total_win: 0,
                    bet_value: this.bet_value
                }
            }
            else if (fish.fishType == 77) {
                response = {
                    cmd: 'sk_12',
                    sys: 'skill',
                    sn: sn,
                    data: {
                        bet: this.bet_value,
                        seat: this.seatId,
                        id: fish.id,
                        crab: fish.id
                    }
                };
    
                response.data.ci = [0, 0, 0];
                var odds = 1;
                if (expectedOdds >= 30 && expectedOdds < 60) {
                    odds = 30;
                    response.data.ci[0] = 0;
                }
                else if (expectedOdds >= 60 && expectedOdds < 90) {
                    odds = 60;
                    response.data.ci[0] = 2;
                }
                else if (expectedOdds >= 90 && expectedOdds < 120) {
                    odds = 90;
                    response.data.ci[0] = 4;
                }
                else if (expectedOdds >= 120 && expectedOdds < 150) {
                    odds = 120;
                    response.data.ci[0] = 6;
                }
                else if (expectedOdds >= 150 && expectedOdds < 300) {
                    odds = 150;
                    response.data.ci[0] = 1;
                    response.data.ci[1] = 0;
                }
                else if (expectedOdds >= 300 && expectedOdds < 450) {
                    odds = 300;
                    response.data.ci[0] = 3;
                    response.data.ci[1] = 2;
                }
                else if (expectedOdds >= 450 && expectedOdds < 500) {
                    odds = 450;
                    response.data.ci[0] = 5;
                    response.data.ci[1] = 4;
                }
                else if (expectedOdds >= 500 && expectedOdds < 800) {
                    odds = 500;
                    response.data.ci[0] = 7;
                    response.data.ci[1] = 5;
                    response.data.ci[2] = 0;
                }
                else if (expectedOdds >= 800) {
                    odds = 800;
                    response.data.ci[0] = 7;
                    response.data.ci[1] = 5;
                    response.data.ci[2] = 2;
                }
                response.data.odds = odds;
                response.data.win = odds * this.bet_value;
                response.data.c0 = [30, 0, 60, 0, 90, 0, 120, 0];
                response.data.c1 = [150, 0, 300, 0, 450, 0];
                response.data.c2 = [500, 0, 800];
                this.balance += odds * this.bet_value;
                await this.updateBalance();
                await this.updateBankDirect(0, odds * this.bet_value);
            }        
    
            sendWSMessage(this.socket, response);
            this.sendToOtherUsers(response);
        }
    }

    async lightingChainFishProcess(fish) {
        var flashSkill = {
            cmd: 'sk_flash',
            sys: 'skill',
            data: {
                seat: this.seatId,
                bet: this.bet_value,
                id: fish.id
            }
        }
        var tempFishInfo = fish;
        if (tempFishInfo != undefined) {
            flashSkill.data.odds = tempFishInfo.odds;
            flashSkill.data.type = tempFishInfo.fishType;
            sendWSMessage(this.socket, flashSkill);
            this.sendToOtherUsers(flashSkill);
            var flashLink = {
                cmd: 'sk_3',
                sys: 'skill',
                data: {
                    seat: this.seatId
                }
            };
            var normalFishes = this.room.fishInfos.filter(x => x.fishType > 0 && x.fishType < 18);
            var linkedCnt = 0;
            var limit = getRandomInt(6, 11);
            if (normalFishes.length < 5)
                linkedCnt = normalFishes.length;
            else
                linkedCnt = getRandomInt(5, limit > normalFishes.length ? normalFishes.length : limit);
            var total_win = tempFishInfo.odds * this.bet_value;
            var fish_dead_dict = {};
            for (var i = 0; i < linkedCnt; i++) {
                var tempLinked = normalFishes[i];
                total_win += tempLinked.odds * this.bet_value;
                fish_dead_dict[tempLinked.id] = { coin: tempLinked.odds * this.bet_value };
            }
            flashLink.data.fish_dead_dict = fish_dead_dict;
            this.balance += total_win;
            await this.updateBalance();
            flashLink.data.total_win = total_win;
            flashLink.data.credits = this.balance;
            await this.updateBankDirect(total_win, 0);
            sendWSMessage(this.socket, flashLink);
            this.sendToOtherUsers(flashLink);

            this.room.resetHit(fish, this, total_win);
        }
    }

    async vortexFishProcess(fish) {
        var windSkill = {
            cmd: 'sk_wind',
            sys: 'skill',
            data: {
                bet: this.bet_value,
                seat: this.seatId,
                fish: [],
                odds: 0,
                win: 0
            }
        };
        var fishType = fish.fishType - 200;
        if (fish.fishType == 208)
            fishType = 9;
        else if (fish.fishType == 209)
            fishType = 10;
        else if (fish.fishType == 210)
            fishType = 11;
        var sameFishes = this.room.fishInfos.filter(x => x.fishType == fishType);
        sameFishes.forEach(oneFish => {
            windSkill.data.fish.push(oneFish.id);
            this.room.fishOut(oneFish.id);
            windSkill.data.win += oneFish.odds * this.bet_value;
            windSkill.data.odds += oneFish.odds;
        });
        this.room.fishOut(fish.id);
        windSkill.data.fish.push(fish.id);
        windSkill.data.win += fish.odds * this.bet_value;
        windSkill.data.odds += fish.odds;
        this.balance += windSkill.data.win;
        await this.updateBalance();
        await this.updateBankDirect(windSkill.data.win, 0);
        sendWSMessage(this.socket, windSkill);
        this.sendToOtherUsers(windSkill);
        this.room.resetHit(fish, this, windSkill.data.win);
    }

    async normalFishMultiProcess(fishes, sn, cmd, id, bet) {
        var odds = 0;
        var catchedFishes = [];

        for (var k = 0; k < fishes.length; k++) {
            var fishId = fishes[k];
            var fish = this.room.fishInfos.find(x => x.id == fishId);    
            if (fish != undefined && fish.fishType >= 200 && fish.fishType <= 210) { //vortex fishes
                return fish.id;
            }
            if (fish != undefined && fish.fishType >= 300 && fish.fishType <= 309) { //lighting chain fishes
                return fish.id;
            }

            if (fish != undefined && fish.fishTypeGroup == 'normal') {
                var eachOdd = 0;
                if (fish.odds != fish.maxOdds)
                    eachOdd = getRandomInt(fish.odds, fish.maxOdds);
                else
                    eachOdd = fish.odds;
                
                var winningWave = redisBridge.isWinningWave(this);
                var bulletEnought = this.room.isEnoughBulletHit(fish, this.getBetWinCondition(), this);
                
                var killCondition = bulletEnought && winningWave;
                if(fish.fishType <= 16)
                {
                    killCondition = bulletEnought; //small fishes and some crabs must be dead with only bullet hit condition
                }
                if(killCondition)
                {
                    odds += eachOdd;
                    catchedFishes.push([fish.id, eachOdd]);
                    this.room.resetHit(fish, this, eachOdd * this.bet_value);
                }
            } else if (fish != undefined && fish.fishTypeGroup != 'normal') {
                return fish.id;                
            }
        }
        if (catchedFishes.length <= 0) {
            return -1;
        }
        if (catchedFishes.length > 0 || this.isDebug) {
            var winVal = 0;
            winVal = this.bet_value * odds;
            
            this.balance += winVal;
            await this.updateBalance();
            await this.updateBankDirect(winVal, 0);
            var weaponReply = { data: {} }
            weaponReply.data[8] = {};
            weaponReply.cmd = cmd;
            weaponReply.sys = 'skill';
            weaponReply.sn = sn;
            weaponReply.data[17] = false; //feature
            weaponReply.data[2] = this.seatId;
            weaponReply.data.timestamp = new Date().getTime();
            weaponReply.data[3] = this.balance;
            weaponReply.data[10] = odds;
            weaponReply.data[1] = id;
            weaponReply.data[9] = bet;
            weaponReply.data.hit_fish_id_list = [];

            catchedFishes.forEach(catchedFish => {
                this.room.fishOut(catchedFish[0]);
                var fishDeadInfo = {};
                fishDeadInfo[10] = catchedFish[1];
                fishDeadInfo[11] = catchedFish[1] * this.bet_value;
                fishDeadInfo[13] = {};
                fishDeadInfo[13].MF100001 = 0;
                fishDeadInfo[13].MF100002 = 0;
                weaponReply.data[8][catchedFish[0]] = fishDeadInfo;
                weaponReply.data.hit_fish_id_list.push(catchedFish[0]);

            });

            weaponReply.data.betRange = this.gameBetRange;
            weaponReply.data.result = 0;
            weaponReply.data.puzzle_ack = {};
            // console.log("weapon response w2 win: " + JSON.stringify(weaponReply));
            sendWSMessage(this.socket, weaponReply);
            this.sendToOtherUsers(weaponReply);
            return -2;
        }
        return -1;
    }

    async normalFishProcess(fish, isSkill24, weaponReq, skill24Req, killCondition) {        
        if (killCondition || this.isDebug) {
            var winVal = 0;
            var odds = fish.odds;
            if (fish.odds != fish.maxOdds)
                odds = getRandomInt(fish.odds, fish.maxOdds);

            this.room.resetHit(fish, this, odds * this.bet_value);
            winVal = this.bet_value * odds;
            this.room.fishOut(fish.id);                
            await this.updateBankDirect(winVal, 0);
            this.balance += winVal;
            await this.updateBalance();
            var weaponReply = { data: {} }
            weaponReply.data[8] = {};
            if (!isSkill24) {
                weaponReply.cmd = weaponReq.cmd;
                weaponReply.sys = weaponReq.sys;
                weaponReply.sn = weaponReq.sn;
                weaponReply.data[1] = weaponReq.data[1]; //id
                weaponReply.data[9] = weaponReq.data[9]; //bet
            }
            else {
                weaponReply.cmd = skill24Req.cmd;
                weaponReply.sys = skill24Req.sys;
                weaponReply.sn = skill24Req.sn;
                weaponReply.data[1] = skill24Req.data[1];
                weaponReply.data[9] = skill24Req.data[9];
            }
            weaponReply.data[17] = false; //feature
            weaponReply.data[2] = this.seatId;
            weaponReply.data.timestamp = new Date().getTime();
            weaponReply.data[3] = this.balance;
            weaponReply.data[10] = odds;
            var fishDeadInfo = {};
            fishDeadInfo[10] = odds;
            fishDeadInfo[11] = winVal;
            fishDeadInfo[13] = {};
            fishDeadInfo[13].MF100001 = 0;            
            fishDeadInfo[13].MF100002 = 0;
            if(this.freeze < 5 && getRandomInt(0, 100) < 5)
            {
                fishDeadInfo[13].MF100001 = 1;
            }
            if(this.summon < 5 && getRandomInt(0, 100) < 5)
            {
                fishDeadInfo[13].MF100002 = 1;
            }
            weaponReply.data[8][fish.id] = fishDeadInfo;
            weaponReply.data.hit_fish_id_list = [fish.id];
            weaponReply.data.betRange = this.gameBetRange;
            weaponReply.data.result = 0;
            weaponReply.data.puzzle_ack = {};
            // console.log("weapon response w2 win: " + JSON.stringify(weaponReply));
            this.room.sendToAllUsers(weaponReply);
            if(fishDeadInfo[13].MF100001 > 0)
            {
                this.freeze++;
            }
            if(fishDeadInfo[13].MF100002 > 0)
            {
                this.summon++;
            }
            return true;
        }
        return false;
    }

    assignBigSmallBombCount(){
        var smallLimit = 500;   
        if (this.multiBombSkillSet.expectedOdds < smallLimit)
        {
            this.multiBombSkillSet.big_bomb_times_target = 0;
            smallLimit = this.multiBombSkillSet.expectedOdds;
        }
        else
        {
            if(this.multiBombSkillSet.expectedOdds - smallLimit <= 200)
                this.multiBombSkillSet.big_bomb_times_target = getRandomInt(4, 6);
            else
                this.multiBombSkillSet.big_bomb_times_target = getRandomInt(7, 10);
        }
        if(this.multiBombSkillSet.big_bomb_times_target > 0)
            smallLimit = parseInt(this.multiBombSkillSet.expectedOdds * 0.2);
        this.multiBombSkillSet.small_bomb_times_target = getRandomInt(4, 6);
        this.multiBombSkillSet.total_win = 0;
        this.multiBombSkillSet.bomb_exploded = 0;
        this.multiBombSkillSet.small_bomb_times_cnt = this.multiBombSkillSet.small_bomb_times_target;
        this.multiBombSkillSet.big_bomb_times_cnt = this.multiBombSkillSet.big_bomb_times_target;            
        this.multiBombSkillSet.small_bomb_limit = parseInt(smallLimit / this.multiBombSkillSet.small_bomb_times_target);
        if(this.multiBombSkillSet.big_bomb_times_target > 0)
            this.multiBombSkillSet.big_bomb_limit = parseInt((this.multiBombSkillSet.expectedOdds - smallLimit) / this.multiBombSkillSet.big_bomb_times_target);
    }

    async updateBalance() {
        await redisBridge.updateBalance(this);
    }

    async updateBank(bet_amount) //update bank when bet
    {
        if(this.socket.readyState === 1)
            await redisBridge.updateBank(this, bet_amount);
    }

    async updateBankDirect(fish, fishSkill) //update bank when win
    {
        if(this.socket.readyState === 1)
        {
            var normal = fish * 0.01;
            var skill = fishSkill * 0.01;
            await redisBridge.updateBankDirect(this, normal, skill);
        }
    }

    chainFishProcess(hitedFish)
    {
        this.room.fishOut(hitedFish.id);
        var bombCnt = getRandomInt(2,6);
        var expectedRate = getRandomInt(100,300);
        var nowDate = new Date().getTime();
        var hitedResponse = {
            message: {
                bombs: [{
                    pos: this.seatId + 1,
                    points:[{x:200,y:100},{x:400,y:600}],
                    type:hitedFish.classId,
                    fish_id: hitedFish.id,
                    id: 1,
                    uid: this.userId,
                    born_time: nowDate,
                    dead_time: nowDate + 20*1000,
                    brate:this.bet_value * this.coinrate / 10,
                    bomb_cnt: 1,
                    use_rate: hitedFish.odds * this.bet_value * this.coinrate / 10,
                    rate: expectedRate,
                    flag:1,
                    num:1,
                    class_id: hitedFish.classId
                }]
            },
            succ: true,
            errinfo: 'ok',
            type: 'increasebombs'
        };
        this.multiBombSkillSet = {
            num: 1,
            bombCnt: bombCnt,
            totalWin: hitedFish.odds * this.bet_value,
            skillName:'chain_skill',
            fishId: hitedFish.id,
            type: hitedFish.classId,
            born_time: nowDate,
            dead_time: nowDate + 20*1000,
            expectedRate: expectedRate,
            oddPerBomb: expectedRate,
            fishType: hitedFish.fishType
        };
        this.multiBombSkillSet.bet_value = this.bet_value;
        this.sendCurPlayer();
        sendWSMessage(this.socket,hitedResponse);
        this.sendToOtherUsers(hitedResponse);
        this.sendBalance();
        
        this.updateBalance();
        this.updateBankDirect(0,hitedFish.odds * this.bet_value);
        //
    }

    async vortexFishProcessGF(hitedFish, gameData)
    {
        this.room.fishOut(hitedFish.id);
        this.sendCurPlayer();
        var hitedReply = [];
        hitedReply.push({
            uid: hitedFish.id,
            score: hitedFish.odds * this.bet_value * this.coinrate/10,
            rate:hitedFish.odds,
            ext:hitedFish.ext,
            fish_id: 0
        });

        var sameFishes = this.room.fishInfos.filter(x => x.fishId == hitedFish.fishId);
        for(var i = 0; i < sameFishes.length; i++)
        {
            hitedReply.push({
                uid: sameFishes[i].id,
                score: sameFishes[i].odds * this.bet_value * this.coinrate/10,
                rate:sameFishes[i].odds,
                ext:sameFishes[i].ext,
                fish_id: 0
            });
        }
        var win = (hitedFish.odds * this.bet_value) * (sameFishes.length + 1);
        this.balance += win;
        this.room.resetHit(hitedFish, this, win);
        await this.updateBalance();
        await this.updateBankDirect(0,hitedFish.odds* this.bet_value);
        if (hitedReply.length > 0)
        {
            var bulletId = gameData.message.bulletid;
            if (bulletId == undefined)
            {
                bulletId = gameData.message.fblist[0].bulletid;
            }
            var hitedResponse = {
                message: {
                    bulletid: this.seatId + '_' + bulletId,
                    pos: this.seatId + 1,
                    fishes: hitedReply
                },
                succ: true,
                errinfo: 'ok',
                type: 'hitsprites'
            };
        }
        sendWSMessage(this.socket,hitedResponse);
        this.sendToOtherUsers(hitedResponse);
        this.sendCurPlayer();
        this.sendBalance();
    }

    bossFishProcessGF(hitedFish)
    {
        this.room.fishOut(hitedFish.id);
        this.room.resetHit(hitedFish, this, hitedFish.odds * this.bet_value);
        if (hitedFish.fishId == 22 || hitedFish.fishId == 23 || hitedFish.fishId == 26)
        {
            var expectedRate = hitedFish.odds;
            var bombCnt = Math.round(expectedRate/200);
            if (bombCnt >= 4)
            {
                bombCnt = 4;
            }else if (bombCnt <= 2)
            {
                bombCnt = 2;
            }
            var nowDate = new Date().getTime()-6255;
            this.multiBombSkillSet = {
                num: bombCnt,
                bombCnt:1,
                totalWin:0,
                skillName:'22_skill',
                fishId: hitedFish.id,
                type: hitedFish.fishId,
                born_time: nowDate,
                dead_time: nowDate + 20*1000,
                expectedRate: expectedRate,
                points:[],
                ext: 0
            };
            if (hitedFish.fishId == 23)
            {
                nowDate = new Date().getTime();
                this.multiBombSkillSet.born_time = nowDate ;
                this.multiBombSkillSet.dead_time = this.multiBombSkillSet.born_time + 16* 1000;
            }else if (hitedFish.fishId == 26)
            {
                this.multiBombSkillSet.born_time = new Date().getTime();
                this.multiBombSkillSet.dead_time = this.multiBombSkillSet.born_time + 9* 1000;
            }
            var hitedResponse = {
                message: {
                    bombs: [{
                        pos: this.seatId + 1,
                        points:[],
                        type:hitedFish.fishId,
                        fish_id: hitedFish.id,
                        id: 1,
                        uid: this.userId,
                        born_time: this.multiBombSkillSet.born_time,
                        dead_time: this.multiBombSkillSet.dead_time,
                        brate:this.bet_value * this.coinrate / 10,
                        bomb_cnt: 1,
                        use_rate: 0,
                        rate: expectedRate,
                        flag:1,
                        num:bombCnt,
                        class_id: hitedFish.fishId,
                        ext:0
                    }]
                },
                succ: true,
                errinfo: 'ok',
                type: 'increasebombs'
            };
            console.log('Num:' + hitedResponse.message.bombs[0].num);
            
            if (hitedFish.fishId == 23)
            {
                this.multiBombSkillSet.skillName = '23_skill';
            } else if (hitedFish.fishId == 26)
            {
                this.multiBombSkillSet.skillName = '26_skill';
            }
            
            this.sendCurPlayer();
            sendWSMessage(this.socket,hitedResponse);
            this.sendToOtherUsers(hitedResponse);
            this.sendBalance();
        } else if (hitedFish.fishId == 21 || hitedFish.fishId == 24 || hitedFish.fishId == 25 || hitedFish.fishId == 20 || hitedFish.fishId == 34 || hitedFish.fishId == 35 || hitedFish.fishId == 40)
        {
            var nowDate = new Date().getTime();
            var expectedRate = hitedFish.odds;
            var points = [];
            bombCnt = Math.round(expectedRate / 100);
            if (bombCnt > 9)
            {
                bombCnt = 8; 
            }
            this.multiBombSkillSet = {
                bombCnt:bombCnt,
                num: bombCnt,
                totalWin:0,
                skillName:'21_skill',
                type: hitedFish.fishId,
                born_time: nowDate,
                dead_time: nowDate + bombCnt * 3912,
                del_time: nowDate + bombCnt * 3912 - 1500,
                fishId: hitedFish.id,
                expectedRate: expectedRate,
                points: points,
                ext: 0
            };

            if (hitedFish.fishId == 40)
            {                
                
                if (expectedRate < 300)
                {
                    bombCnt = 4;
                    this.multiBombSkillSet.bombCnt = 4;
                    this.multiBombSkillSet.num = 2;
                    this.multiBombSkillSet.ext = 0;
                    for(var i = 0; i < this.multiBombSkillSet.num; i++)
                    {
                        points.push({
                            x: getRandomInt(300,700),
                            y: getRandomInt(200,400)
                        });
                    }
                }else{
                    bombCnt = 11;
                    if (expectedRate >= 300 && expectedRate < 500)
                    {
                        bombCnt = 11;
                    }else if (expectedRate >= 500 && expectedRate < 600 )
                    {
                        bombCnt = 12;
                    }else if (expectedRate >= 600 && expectedRate < 700)
                    {
                        bombCnt = 13;
                    }else if (expectedRate >= 700 && expectedRate < 850)
                    {
                        bombCnt = 14;
                    }else if (expectedRate >= 850)
                    {
                        bombCnt = 15;
                    }
                    bombCnt == 11;
                    var pointsCnt = 8;  //
                    if (bombCnt == 11)
                    {
                        pointsCnt = 7;
                        this.multiBombSkillSet.bombCnt = 11;
                        this.multiBombSkillSet.num = 4;
                        this.multiBombSkillSet.ext = 3;
                    }else if (bombCnt == 12) 
                    {
                        pointsCnt = 8;
                        this.multiBombSkillSet.bombCnt = 12;
                        this.multiBombSkillSet.num = 4;
                        this.multiBombSkillSet.ext = 4;
                    }else if (bombCnt == 13)
                    {
                        pointsCnt = 9;
                        this.multiBombSkillSet.bombCnt = 13;
                        this.multiBombSkillSet.num = 4;
                        this.multiBombSkillSet.ext = 5;
                    }else if (bombCnt == 14)
                    {
                        pointsCnt = 10;
                        this.multiBombSkillSet.bombCnt = 14;
                        this.multiBombSkillSet.num = 4;
                        this.multiBombSkillSet.ext = 6;
                    }else if (bombCnt == 15)
                    {
                        pointsCnt = 10;
                        this.multiBombSkillSet.bombCnt = 15;
                        this.multiBombSkillSet.num = 5;
                        this.multiBombSkillSet.ext = 5;
                    }
                    for(var i = 0; i < pointsCnt ; i++)
                    {
                        points.push({
                            x: getRandomInt(300,700),
                            y: getRandomInt(200,400)
                        });
                    }
                }
            }

            if (hitedFish.fishId == 24) 
            {
                this.multiBombSkillSet.skillName = '24_skill';
                this.multiBombSkillSet.dead_time = nowDate + bombCnt * 4150 + 1251;
            }else if (hitedFish.fishId == 25) 
            {
                this.multiBombSkillSet.dead_time = nowDate + bombCnt*3080 + 3250
                this.multiBombSkillSet.skillName = '25_skill';
            }else if (hitedFish.fishId == 20)
            {
                this.multiBombSkillSet.skillName = '20_skill';
            }else if (hitedFish.fishId == 34)
            {
                this.multiBombSkillSet.skillName = '34_skill';
                this.multiBombSkillSet.born_time = nowDate;
                this.multiBombSkillSet.dead_time = nowDate + bombCnt * 4501 ;
            }else if (hitedFish.fishId == 35)
            {
                bombCnt = bombCnt * 4;
                this.multiBombSkillSet.bombCnt = bombCnt;
                this.multiBombSkillSet.num = bombCnt;
                this.multiBombSkillSet.dead_time = nowDate + bombCnt * 1068 + 5584;
                this.multiBombSkillSet.num = 1;
                this.multiBombSkillSet.skillName = '35_skill';
            }else if (hitedFish.fishId == 40)
            {
                if (this.multiBombSkillSet.bombCnt > 4)
                {
                    this.multiBombSkillSet.oddPerBombForSmall = (expectedRate*0.3)/8;
                    this.multiBombSkillSet.oddsPerBombForBig = (expectedRate*0.7)/this.multiBombSkillSet.ext;
                    this.multiBombSkillSet.oddPerBombForBF = 0;
                }else{
                    this.multiBombSkillSet.ext = 0;
                    this.multiBombSkillSet.oddPerBombForBF = expectedRate / bombCnt;
                    this.multiBombSkillSet.oddPerBombForSmall = 0;
                    this.multiBombSkillSet.oddsPerBombForBig = 0;
                }
                //
                this.multiBombSkillSet.dead_time = nowDate + ((this.multiBombSkillSet.bombCnt - this.multiBombSkillSet.ext) * 2570)  + 995 + 1600* this.multiBombSkillSet.ext + 2225;
                this.multiBombSkillSet.skillName = '40_skill';
            }else if (hitedFish.fishId == 21)
            {
                this.multiBombSkillSet.skillName = '21_skill';
                this.multiBombSkillSet.dead_time = nowDate + bombCnt * 3100 + 3250;
            }
            this.multiBombSkillSet.oddPerBomb = expectedRate / bombCnt;
            var hitedResponse = {
                message: {
                    bombs: [{
                        pos: this.seatId + 1,
                        type:hitedFish.fishId,
                        fish_id: hitedFish.id,
                        uid: this.userId,
                        id: 1,
                        points:points,
                        born_time: this.multiBombSkillSet.born_time,
                        dead_time: this.multiBombSkillSet.dead_time,
                        del_time: this.multiBombSkillSet.del_time,
                        brate: this.bet_value * this.coinrate/10,
                        bomb_cnt: bombCnt,
                        use_rate: 0,
                        num:this.multiBombSkillSet.num,
                        flag:1,
                        rate: expectedRate,
                        type: hitedFish.fishId,
                        shot_time: 0,
                        tons_list:[],
                        class_id: hitedFish.fishId,
                        ext: this.multiBombSkillSet.ext,
                        cond_rate: 0,
                    }]
                },
                succ: true,
                errinfo: 'ok',
                type: 'increasebombs'
            };
            this.sendCurPlayer();
            sendWSMessage(this.socket,hitedResponse);
            this.sendToOtherUsers(hitedResponse);
            this.sendBalance();
        }
        this.multiBombSkillSet.bet_value = this.bet_value;
    }
}
module.exports = { Player }