const { shuffle } = require("lodash");
const { Common } = require("./Common");
const { getRandomInt } = require("./utils");
const mysql_tools = require('./mysql_tools');
var dbconn = require('./DBConn').dbconn;
class RTPWaver
{
    constructor(avgRTP, duration, listCount, games, keepBetWin){
        this.avgRTP = avgRTP;
        this.tick = 0;
        this.listCount = listCount;
        this.rtpDuration = duration; //min
        this.rtpList = {};
        this.is_demo = 0;
        this.keepBetWin = keepBetWin;
        this.games = games;
        this.games.forEach(async gameType => {
            //read current game rtp
            var gameName = Common.gameNames[gameType];
            var gameInfo = await mysql_tools.sendQuery(dbconn, "select A.rtp, B.wavesize from w_games A inner join w_game_win_settings B on A.id = B.gameid where A.name = ? and A.shop_id = 0", [ gameName ]);
            if(gameInfo.length > 0)
            {
                this.rtpList[gameType] = {
                    rtpList: this.generateRandomRTPQueue(gameInfo[0].rtp),
                    rtpIndex: 0,
                    bet: 0,
                    win: 0,
                    wavesize: gameInfo[0].wavesize
                };

                if(this.is_demo == 0)
                    console.log("rtp list for " + gameType + ": gameRTP: " + gameInfo[0].rtp + " wave: " + JSON.stringify(this.rtpList[gameType]));
            }
        });

        // this.rtpIndex = 0;        
        // this.intervalID = setInterval(() => {
        //     this.update();
        // }, 1000 * 60);
    }

    setBet(_bet, gameType)
    {
        this.rtpList[gameType].bet += _bet;
        this.checkCurrentWaveFinished(gameType);
    }

    setWin(_win, gameType)
    {
        this.rtpList[gameType].win += _win;
    }

    // update()
    // {
    //     this.tick++;
    //     if(this.tick % this.rtpDuration == 0)
    //     {
    //         var cur_rtp = 0;
    //         if(this.bet != 0)
    //         {
    //             cur_rtp = this.win / this.bet * 100;
    //         }
    //         if(this.is_demo == 0)
    //             console.log("rtp for last " + this.rtpDuration + " mins: " + cur_rtp.toFixed(2));

    //         this.rtpIndex = (this.rtpIndex + 1) % this.listCount;
    //         if(!this.keepBetWin)
    //         {
    //             this.bet = 0;
    //             this.win = 0;
    //         }            
    //     }
    //     if(this.tick >= this.rtpDuration * this.listCount)
    //     {            
    //         this.games.forEach(async element => {
    //             //read current game rtp
    //             var gameName = Common.gameNames[element];
    //             var gameInfo = await mysql_tools.sendQuery(dbconn, "SELECT rtp from w_games where `name` = ? and shop_id = 0", [ gameName ]);
    //             if(gameInfo.length > 0)
    //             {
    //                 this.rtpList[element] = this.generateRandomRTPQueue(gameInfo[0].rtp);
    //                 if(this.is_demo == 0)
    //                     console.log("rtp list for " + element + ": gameRTP: " + gameInfo[0].rtp + " wave: " + JSON.stringify(this.rtpList[element]));
    //             }                
    //         });
    //         this.tick = 0;
    //     }
    // }

    async checkCurrentWaveFinished(gameType)
    {        
        var gameObject = this.rtpList[gameType];
        if(gameObject.bet >= gameObject.wavesize)
        {
            var cur_rtp = 0;
            if(gameObject.bet != 0)
            {
                cur_rtp = gameObject.win / gameObject.bet * 100;
            }
            if(this.is_demo == 0)
            console.log("moving cur_rtp: " + cur_rtp + " wave_rtp: " + gameObject.rtpList[gameObject.rtpIndex]);
            gameObject.bet = 0;
            gameObject.win = 0;
            gameObject.rtpIndex++;
            if(gameObject.rtpIndex >= gameObject.rtpList.length)
            {
                gameObject.rtpIndex = 0;
                var gameName = Common.gameNames[gameType];
                var gameInfo = await mysql_tools.sendQuery(dbconn, "select A.rtp, B.wavesize from w_games A inner join w_game_win_settings B on A.id = B.gameid where A.name = ? and A.shop_id = 0", [ gameName ]);
                if(gameInfo.length > 0)
                {
                    gameObject.rtpList = this.generateRandomRTPQueue(gameInfo[0].rtp);
                    gameObject.rtpIndex = 0;                                        
                    gameObject.wavesize = gameInfo[0].wavesize;
                }
                if(this.is_demo == 0)
                console.log("generated new list for " + gameType + " " + JSON.stringify(gameObject));
            }            
            if(this.is_demo == 0)
            console.log("moving to next wave, wave rtp: " + gameObject.rtpList[gameObject.rtpIndex]);
        }
    }

    isWinningWave(gameType)
    {
        var gameObject = this.rtpList[gameType];

        var cur_rtp = 0;
        if(gameObject.bet != 0)
        {
            cur_rtp = (gameObject.win / gameObject.bet * 100).toFixed(2);
        }
        var result = true;
        var rtpList = gameObject.rtpList;
        if(cur_rtp < rtpList[gameObject.rtpIndex])
        {            
            result = true;
        }
        else
        {
            result = false;
        }
        if(this.is_demo == 0)
            console.log(gameType + " target rtp: " + rtpList[gameObject.rtpIndex] + " current rtp: " + cur_rtp + " result: " + result);
        return result;
    }

    generateRandomRTPQueue(avgRTP)
    {       
        var total = avgRTP * this.listCount;
        var rtpList = [];

        var highRtpRandomness = getRandomInt(0, 3);
        var highCnt = 0;
        if(avgRTP <= 20)
            highRtpRandomness = 0;
        if(highRtpRandomness == 0)
            highCnt = 4;
        else if(highRtpRandomness == 1)
            highCnt = 3;
        else 
            highCnt = 2;      

        for(var i = 0; i < this.listCount; i++)
        {
            var max = total;
            var min = 20;
            // if(this.keepBetWin)
            // {
            //     min = this.avgRTP;
            // }
            if(highRtpRandomness == 0)
            {
                //generate 4 200 
                if(i < highCnt)                    
                {
                    min = 100;
                    max = 150;
                }
                    
            }
            else if(highRtpRandomness == 1)
            {
                //generate 3 300
                if(i < highCnt)
                {
                    min = 150;
                    max = 200;
                }
            }
            else
            {
                //generate 2 400
                if(i < highCnt)
                {
                    min = 200;
                    max = 250;
                }
            }

            if(i >= highCnt)
            {
                max = total / (this.listCount - i);
            }
            if(max > 400)
                max = 400;
            if(max < min)
                max = min;
            
            var rtp = getRandomInt(min, max);
            if(i == this.listCount - 1)
                rtp = max;
            total -= rtp;
            rtpList.push(rtp);
        }
        rtpList = shuffle(rtpList);
        return rtpList;
    }
}

module.exports = {RTPWaver}