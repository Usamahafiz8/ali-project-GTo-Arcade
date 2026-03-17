const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;
class GameRoomCS extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'CS';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.betPerOddIncrease = 6;        
       
        this.scriptList = [2009, 1017, 1018, 17, 2509, 2010, 1019, 1020, 17, 2510];
        this.scriptName = {
            2009: 'BG_09',
            1017: 'CIRCUS_PIG_ENTER',
            1018: 'CIRCUS_PIG_LEAVE',
            17: 'FISH_LEAVING',
            2509: 'BG_09_LEAVE',
            2010: 'BG_10',
            1019: 'CIRCUS_TIGER_ENTER',
            1020: 'CIRCUS_TIGER_LEAVE',            
            2510: 'BG_10_LEAVE'
        }
        this.scriptPeriods = {
            2009: 294,
            1017: 200,
            1018: 2,
            17: 2,
            2509: 2,
            2010: 294,
            1019: 200,
            1020: 2,
            17: 2,
            2510: 3
        };
        
        this.scriptIndex = 0;
        this.lastScript = this.scriptList[this.scriptIndex];
        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.bossFormat.push({id: 78, fishType: 78, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Thunder dragon
        this.bossFormat.push({id: 80, fishType: 80, prizeRatio: 3, odds: 100, maxOdds: 1000, fishTypeGroup: 'boss'}); //Ghost ship
        this.bossFormat.push({id: 93, fishType: 93, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Circus clown
        this.bossFormat.push({id: 94, fishType: 94, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Circus Pig
        this.bossFormat.push({id: 95, fishType: 95, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Circus Tiger
        this.accumulatedOdd = 150;
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
                case 2009:
                case 2010:      
                    if(this.scriptPrepareTime > 0)
                        return;          
                    //for bg03 and bg06, generate normal fishes
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        //generate skill fish
                        this.generateSkillFish();
                        //generate clown
                        if (!this.checkSpecialTypeFish(93) && (new Date().getTime() - this.accumulatingFishDeadTime) > 60000)
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 93, 'boss'));                    
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
                case 1017:
                    if(this.scriptPrepareTime > 0)
                        return;          
                    this.generateNormalFishes();
                    this.generateSkillFish();

                    //generate pig
                    if(!this.checkSpecialTypeFish(94) && this.activeSkill != 'sk_CircusPig_bomb')
                    {
                        this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 94, 'boss'));
                    }                  
                    break;
                case 1019:
                    if(this.scriptPrepareTime > 0)
                        return;                    
                    this.generateNormalFishes();
                    this.generateSkillFish();

                    //generate tiger
                    if(!this.checkSpecialTypeFish(95))
                    {
                        this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 95, 'boss'));
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

            if (this.scriptList[this.scriptIndex] == 1019 && this.scriptTime <= 2)
            {
                if(this.activeSkill == 'sk_CircusTiger')
                {
                    this.scriptTime += 10;
                }
            }

            if(this.scriptTime <= 0)
            {
                this.scriptIndex = (this.scriptIndex + 1) % this.scriptList.length;
                this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
                this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
                if (this.scriptList[this.scriptIndex] == 1013)
                {
                    this.scriptPrepareTime = 10;
                }
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
module.exports = {GameRoomCS};