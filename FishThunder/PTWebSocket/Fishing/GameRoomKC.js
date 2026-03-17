const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;
class GameRoomKC extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'KC';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.betPerOddIncrease = 6;        
       
        this.scriptList = [4013, 4201, 4013, 4203, 4014, 4202, 4012, 4201, 4013, 4203, 4013, 4014, 4202, 4012, 4011, 17, 4111];
        this.scriptName = {
            4011: 'BG_11',
            4012: 'BG_12',
            4013: 'BG_13',
            4014: 'BG_14',
            4201: 'ESQUID_ENTER',
            4202: 'EPHOENIX_ENTER',
            4203: 'EDRAGON_ENTER',
            17: 'FISH_LEAVING',
            4111: 'BG_11_LEAVE'
        }
        this.scriptPeriods = {
            4011: 70,
            4012: 70,
            4013: 70,
            4014: 70,
            4201: 70,
            4202: 70,
            4203: 70,
            17: 1,
            4111: 1
        };
        
        this.scriptIndex = 1;        
        this.lastScript = this.scriptList[this.scriptIndex];
        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.bossFormat.push({id: 111, fishType: 111, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Eternal Squid
        this.bossFormat.push({id: 112, fishType: 112, prizeRatio: 3, odds: 100, maxOdds: 1000, fishTypeGroup: 'boss'}); //Eternal Phoenix
        this.bossFormat.push({id: 113, fishType: 113, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Flaming Dragon
        this.bossFormat.push({id: 114, fishType: 114, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //General Lobster
        this.bossFormat.push({id: 115, fishType: 115, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Slot Crab        
        this.accumulatedOdd = 0;
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
            switch(script)
            {
                case 4011:
                    //generate slot crab
                    if(!this.checkSpecialTypeFish(115) && this.scriptTime > 10)
                    {
                        this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 115, 'boss'));
                    }                    
                case 4012:      
                case 4013:
                case 4014:                    
                    //for bg03 and bg06, generate normal fishes
                    this.generateNormalFishes();
                    //generate skill fish
                    this.generateSkillFish();
                    //generate lobster
                    if (!this.checkSpecialTypeFish(114) && this.scriptTime > 10)
                    {
                        this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 114, 'boss'));                    
                    }
                    break;
                case 4201:
                    this.generateNormalFishes();
                    this.generateSkillFish();

                    //generate squid
                    if(!this.checkSpecialTypeFish(111) && this.scriptTime > 10)
                    {
                        this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 111, 'boss'));
                    }                  
                    break;
                case 4202:
                    this.generateNormalFishes();
                    this.generateSkillFish();

                    //generate eternal phoenix
                    if(!this.checkSpecialTypeFish(112) && this.scriptTime > 10)
                    {
                        this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 112, 'boss'));
                    }               
                    break;          
                case 4203:
                    this.generateNormalFishes();
                    this.generateSkillFish();

                    //generate eternal dragon
                    if(!this.checkSpecialTypeFish(113) && this.scriptTime > 10 && this.activeSkill != 'sk_RedDragon_bomb')
                    {
                        this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 113, 'boss'));
                    }               
                    break;                 
            }

            var curDate = new Date();
            this.fishInfos.forEach(fish => {
                if(fish.birthDate.getTime() + 45000 <  curDate.getTime() && !fish.isLevelFish)
                {
                    this.fishOut(fish.id);
                }
            });            

            if(this.scriptTime <= 0)
            {
                this.scriptIndex = (this.scriptIndex + 1) % this.scriptList.length;
                this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];                
                this.dispatchScriptEvent(this.scriptName[this.scriptIndex], this.scriptList[this.scriptIndex]);
            }

            if (this.scriptList[this.scriptIndex] == 17 || this.scriptList[this.scriptIndex] == 4111)
            {
                if(this.scriptTime > 3)
                    this.scriptTime = 1;
            }
        }
    }

    fishOut(fishId)
    {
        super.fishOut(fishId);
    }

    getBigSceneId()
    {
        var bgId = this.getSceneId();
        if (bgId > 4200)
        {
            var tempIndex = this.scriptIndex;
            while(this.scriptList[tempIndex] > 4200)
            {
                tempIndex--;
            }
            bgId = this.scriptList[tempIndex];
        }
        return bgId;
    }
}
module.exports = {GameRoomKC};