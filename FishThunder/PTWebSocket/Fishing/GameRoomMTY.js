const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;
class GameRoomMTY extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'MTY';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.betPerOddIncrease = 3;        
       
        this.scriptList = [2007, 1013, 1014, 17, 2507, 2008, 1015, 1016, 17, 2508];
        this.scriptName = {
            2007: 'BG_07',
            1013: 'TURTLE_BOSS_ENTER',
            1014: 'TURTLE_BOSS_LEAVE',
            17: 'FISH_LEAVING',
            2008: 'BG_08',
            1015: 'LION_DANCE_ENTER',
            1016: 'LION_DANCE_LEAVE',
            17: 'FISH_LEAVING',
            2508: 'BG_08_LEAVE'
        }
        this.scriptPeriods = {
            2007: 294,
            1013: 200,
            1014: 2,
            17: 2,
            2507: 3,
            2008: 294,
            1015: 200,
            1016: 2,
            17: 2,
            2508: 3
        };
        
        this.scriptIndex = 0;        
        this.lastScript = this.scriptList[this.scriptIndex];
        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.bossFormat.push({id: 78, fishType: 78, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Thunder dragon
        this.bossFormat.push({id: 80, fishType: 80, prizeRatio: 3, odds: 100, maxOdds: 1000, fishTypeGroup: 'boss'}); //Phoenix
        this.bossFormat.push({id: 86, fishType: 86, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Lion Dance
        this.bossFormat.push({id: 85, fishType: 85, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Dragon Turtle
        this.bossFormat.push({id: 84, fishType: 84, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Lucky Buddha        
        this.accumulatedOdd = 100;
        this.accumulatingFishDeadTime = 0;
        this.mainScriptStartTime = new Date();
        this.intervalID = setInterval(() => {            
            this.run();
        }, 1000);
        this.tideStatus = 'endTide';        
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
                case 2007:
                case 2008:      
                    if(this.scriptPrepareTime > 0)
                        return;          
                    //for bg03 and bg06, generate normal fishes
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        //generate skill fish
                        this.generateSkillFish();
                        //generate lucky buddha
                        if (!this.checkSpecialTypeFish(84) && (new Date().getTime() - this.accumulatingFishDeadTime) > 60000 && getRandomInt(0, 100) < 30 && this.activeSkill != 'sk_LuckyBuddha')
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 84, 'boss'));                    
                        }

                        if(!this.checkSpecialTypeFish(80) && !this.checkSpecialTypeFish(78))
                        {
                            if(getRandomInt(0, 100) < 50)
                                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 80, 'boss'));
                            else
                                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 78, 'boss'));
                        }
                    }
                    break;
                case 1013:
                    if(this.scriptPrepareTime > 0)
                        return;          
                    //for bg03 and bg06, generate normal fishes
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        //generate skill fish
                        this.generateSkillFish();

                        if(!this.checkSpecialTypeFish(85) && getRandomInt(0, 100) < 30 && this.activeSkill != 'sk_TurtleBoss_bomb')
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 85, 'boss'));
                        }
                    }                    
                    break;
                case 1015:
                    if(this.scriptPrepareTime > 0)
                        return;          
                    //for bg03 and bg06, generate normal fishes
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        //generate skill fish
                        this.generateSkillFish();

                        if(!this.checkSpecialTypeFish(86) && getRandomInt(0, 100) < 30 && this.activeSkill != 'sk_LionDance')
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 86, 'boss'));
                        }
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
                if (this.scriptList[this.scriptIndex] == 1013)
                {
                    this.scriptPrepareTime = 10;
                }
                this.dispatchScriptEvent(this.scriptName[this.scriptIndex], this.scriptList[this.scriptIndex]);
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
module.exports = {GameRoomMTY};