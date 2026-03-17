const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;
class GameRoomLS extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'LS';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.betPerOddIncrease = 6;        
       
        this.scriptList = [2001, 1001, 1002, 17, 2501, 2006, 1011, 1012, 17, 2506, 2005, 1009, 1010, 17, 2505, 2006, 1011, 1012, 17, 2506];
        this.scriptName = {
            2001: 'BG_01',
            1001: 'KING_SPIDERCRAB_ENTER',
            1002: 'KING_SPIDERCRAB_LEAVE',
            2501: 'BG_01_LEAVE',
            2006: 'BG_06',
            1011: 'LUCKY_SHAMROCK_ENTER',
            1012: 'LUCKY_SHAMROCK_LEAVE',
            2506: 'BG_06_LEAVE',
            2005: 'BG_05',
            1009: 'BUFFALO_ENTER',
            1010: 'BUFFALO_LEAVE',
            17: 'FISH_LEAVING',
            2505: 'BG_05_LEAVE',
        }
        this.scriptPeriods = {
            2001: 364,            
            1001: 80,
            1002: 1,
            2501: 2,
            2005: 294,
            1009: 200,
            1010: 2,
            17: 3,
            2505: 2,            
            2006: 294,
            1011: 200,
            1012: 1,
            2506: 2
        };
        // this.scriptPeriods = {
        //     2001: 40,            
        //     1001: 200,
        //     1002: 1,
        //     2501: 2,
        //     2005: 40,
        //     1009: 40,
        //     1010: 2,
        //     17: 3,
        //     2505: 2,            
        //     2006: 40,
        //     1011: 40,
        //     1012: 1,
        //     2506: 2
        // };
        this.scriptIndex = 0;        
        this.lastScript = this.scriptList[this.scriptIndex];
        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.bossFormat.push({id: 80, fishType: 80, prizeRatio: 3, odds: 100, maxOdds: 1000, fishTypeGroup: 'boss'}); //Phoenix
        this.bossFormat.push({id: 25, fishType: 25, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Poseidon
        this.bossFormat.push({id: 81, fishType: 81, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Buffalo
        this.bossFormat.push({id: 24, fishType: 24, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Crab
        this.bossFormat.push({id: 82, fishType: 82, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Lucky Shamrock
        this.accumulatedOdd = 0;
        this.mainScriptStartTime = new Date();
        this.intervalID = setInterval(() => {            
            this.run();
        }, 1000);
        this.crabOutTime = new Date();
        // this.tideStatus = 'endTide';
    }

    generateNormalFishes()
    {
        if(this.getTotalNormalOdds() < this.maxRoomNormalOdds)
        {
            var fishGroup = this.fishGroupGenerate(getRandomInt(2,4), normalFishFormat, -1, 'normal');
            this.sendToAllUsers(fishGroup);
            if(this.fishInfos.find(x => x.fishType == 17) == undefined)
            {
                if(getRandomInt(0, 100) < 30)
                    this.sendToAllUsers(this.fishGroupGenerate(getRandomInt(1,2), normalFishFormat, 17, 'normal'));
            }
            if(this.fishInfos.find(x => x.fishType == 18) == undefined)
            {
                if(getRandomInt(0, 100) < 30)
                    this.sendToAllUsers(this.fishGroupGenerate(getRandomInt(1, 2), normalFishFormat, 18, 'normal'));
            }
            if(this.fishInfos.find(x => x.fishType == 15) == undefined)
            {
                if(getRandomInt(0, 100) < 50)
                    this.sendToAllUsers(this.fishGroupGenerate(getRandomInt(1,2), normalFishFormat, 15, 'normal'));
            }
            if(this.fishInfos.find(x => x.fishType == 16) == undefined)
            {
                if(getRandomInt(0, 100) < 50)
                    this.sendToAllUsers(this.fishGroupGenerate(getRandomInt(1,3), normalFishFormat, 16, 'normal'));
            }

            var isVortexFish = false;
            var isLightingFish = false;
            this.fishInfos.forEach(temp => {
                if(temp.fishTypeGroup == 'normal' && vortexFishFormat.find(x => x.id == temp.fishType) != undefined)
                    isVortexFish = true;
                else if(temp.fishTypeGroup == 'normal' && lightingChainFormat.find(x => x.id == temp.fishType) != undefined)
                    isLightingFish = true;
            });
            if(!isVortexFish && getRandomInt(0, 100) < 40)
                this.sendToAllUsers(this.fishGroupGenerate(getRandomInt(1, 2), vortexFishFormat, -1, 'normal'));
            if(!isLightingFish && getRandomInt(0, 100) < 40)
                this.sendToAllUsers(this.fishGroupGenerate(getRandomInt(1, 2), lightingChainFormat, -1, 'normal'));
        }
    }

    generateSkillFish()
    {
        if((new Date().getTime() - this.splashDate.getTime()) / 1000 > 10)
        {
            var isSkill = false;
            try{
                this.fishInfos.forEach(fishInfo => {
                    if(fishInfo.fishTypeGroup == 'skill')
                    {
                        isSkill = true;
                        throw 'Break';
                    }
                });
            }catch(e)
            {
                if(e!= 'Break') throw e;
            }
            if(!isSkill)
                this.sendToAllUsers(this.fishGroupGenerate(1, skillFishFormat, -1, 'skill'));
        }
    }

    generateTide()
    {
        this.tideMaxTime = 0;
        this.armyInfo = [];
        this.tideStatus = 'tiding';
        this.lastTide = new Date();
        var fish1Reply = {cmd: 'f1', sys: 'fish',data: {timestamp: parseInt(new Date().getTime()/1000)}};
        fish1Reply.data[14] = this.scriptList[this.scriptIndex];
        fish1Reply.data[15] = [];
        fish1Reply.data[11] = [];
        var fishInfo = {};
        fishInfo[1] = this.generateFishId(); //id
        fishInfo[9] = this.getCurrentFrame(); //frame;
        fishInfo[6] = 0; //feature
        fishInfo[16] = null; //king_status
        fishInfo[7] = 0; //position
        fishInfo[2] = getRandomInt(400, 411); //type        
        fishInfo[3] = 34; //x
        fishInfo[4] = getRandomInt(2, 500); //y
        fishInfo[5] = getRandomInt(0, 300); //o
        fishInfo[8] = 1;
        fish1Reply.data[11].push(fishInfo);
        this.sendToAllUsers(fish1Reply);
        var tide = gTides.find(x => x.fishType == fishInfo[2]);
        if(tide != undefined)
        {
            for(var i = 0; i < tide.cnt; i++)
            {
                var tideFishDateInfo = tide.fishDataLst[i];
                var curFishInfo = gAllFishInfo.find(x => x.fishType == tideFishDateInfo.fishtype);
                // console.log("tide fish type: " + curFishInfo.fishType + " odds: " + curFishInfo.odds + " id: " + (fishInfo[1] + i + 1));
                if(curFishInfo != undefined)
                {
                    this.armyInfo.push({id: fishInfo[1] + i + 1, fishType: tideFishDateInfo.fishtype, odds: curFishInfo.odds, maxOdds: curFishInfo.maxOdds, birthDate: new Date(), prizeRatio: curFishInfo.prizeRatio, fishTypeGroup: 'normal'});
                    var fishtime = parseFloat(tideFishDateInfo.dispearTime);                            
                    if(fishtime > this.tideMaxTime)
                        this.tideMaxTime = fishtime;
                }
            }
            this.currentFishIndex += tide.cnt + 1;
        }
    }

    run()
    {
        this.update();
        
        if(this.isAvailable)   
        {
            this.allowGenerateFish = false;
            try{
                this.clients.forEach(fishPlayer => {
                    if(fishPlayer.allowFish)
                    {
                        this.allowGenerateFish = true;
                        throw 'Break';
                    }
                });
            }catch(e)
            {
                if(e!='Break') throw e;
            }

            var script = this.scriptList[this.scriptIndex];
            this.scriptTime--;
            if(this.scriptPrepareTime > 0)
                this.scriptPrepareTime--;
            switch(script)
            {
                case 2006:
                case 2005:
                case 2001:
                    if(this.scriptPrepareTime > 0)
                        return;
                    //for bg05 and bg06, generate normal fishes
                    if(this.tideStatus == 'startTide' && this.fishInfos.length <= 3)
                    {
                        this.generateTide();
                    }
                    else if(this.tideStatus == 'tiding' && this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length < 35 && ((new Date()).getTime() - this.mainScriptStartTime.getTime()) >= 20000)
                    {
                        this.tideStatus = 'endTide';                        
                    }
                    if(this.tideStatus == 'endTide')
                    {
                        this.generateNormalFishes();
                    }

                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        this.generateSkillFish();
                        if (!this.checkSpecialTypeFish(80) && !this.checkSpecialTypeFish(25))
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat,getRandomInt(0,100) > 50 ? 80 : 25 , 'boss'));
                        }
                    }

                    break;
                case 1009:
                    this.generateNormalFishes();

                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        this.generateSkillFish();
                        
                        if(!this.checkSpecialTypeFish(81) && getRandomInt(0,100) < 30 && this.activeSkill != 'sk_buffalo_bomb')
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 81, 'boss'));
                    }
                    break;
                case 1001:
                    this.generateNormalFishes();

                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        this.generateSkillFish();
                        if(!this.checkSpecialTypeFish(24) && (new Date().getTime() - this.crabOutTime.getTime()) / 1000 > 20)
                            this.sendToAllUsers(this.fishGroupGenerate(2, this.bossFormat, 24, 'boss'));
                    }
                    break;
                case 1011:
                    if(this.scriptPrepareTime > 0)
                        return;
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        this.generateSkillFish();

                        if(!this.checkSpecialTypeFish(82) && getRandomInt(0,100) < 30  && this.activeSkill != 'sk_lucky_shamrock_bomb')
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 82, 'boss'));
                    }
                    break;                             
            }

            var curDate = new Date();
            this.fishInfos.forEach(fish => {
                if(fish.birthDate.getTime() + 45000 <  curDate.getTime())
                {
                    this.fishOut(fish.id);
                }
            });            

            if(this.scriptTime <= 0)
            {
                this.scriptIndex = (this.scriptIndex + 1) % this.scriptList.length;
                this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
                this.dispatchScriptEvent(this.scriptName[this.scriptIndex], this.scriptList[this.scriptIndex]);
                if(this.scriptList[this.scriptIndex] == 2005 || this.scriptList[this.scriptIndex] == 2006 || this.scriptList[this.scriptIndex] == 2001)
                {
                    this.mainScriptStartTime = new Date();
                    this.tideStatus = 'startTide';
                    this.scriptPrepareTime = 5;
                }
            }
        }
    }

    fishOut(fishId)
    {
        // var fishInfo = this.fishInfos.filter(x => x.id == fishId);
        // if(fishInfo != undefined && fishInfo.length > 0 && fishInfo[0].fishType == 83)
        // {
        //     this.accumulatedOdd = fishInfo[0].odds;        
        //     console.log("out aladdin odd: " + this.accumulatedOdd + " id: " + fishInfo[0].id);
        // }
        
        super.fishOut(fishId);
    }
}
module.exports = {GameRoomLS};