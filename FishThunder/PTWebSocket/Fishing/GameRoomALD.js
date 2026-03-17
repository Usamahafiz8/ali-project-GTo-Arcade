const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;
class GameRoomALD extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'ALD';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.betPerOddIncrease = 3;

        /**
         * script indices
         * 2003: bg03
         * 1011: genie appear
         * 1012: boss leave
         * 17: fish fade out
         * 2503: bg03 leave
         * 2006: bg06
         * 2506: bg06 leave
         */
        this.scriptList = [2003, 1011, 1012, 17, 2503, 2006, 1011, 1012, 17, 2506];
        this.scriptPeriods = {
            2003: 294,
            1011: 200,
            1012: 2,
            17: 2,
            2503: 2,
            2006: 294,
            2506: 2
        }
        this.scriptName = {
            2003: 'BG_03',
            1011: 'ROCK_SKELETON_ENTER',
            1012: 'ROCK_SKELETON_LEAVE',
            17: 'FISH_LEAVING',
            2506: 'BG_06_LEAVE',
            2503: 'BG_03_LEAVE',
            2006: 'BG_06'
        }
        this.scriptIndex = 0;        
        this.lastScript = 2006;
        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.bossFormat.push({id: 80, fishType: 80, prizeRatio: 3, odds: 100, maxOdds: 1000, fishTypeGroup: 'boss'}); //Phoenix
        this.bossFormat.push({id: 25, fishType: 25, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Poseidon
        this.bossFormat.push({id: 78, fishType: 78, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Thunder dragon
        this.bossFormat.push({id: 82, fishType: 82, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //RockSkull
        this.bossFormat.push({id: 83, fishType: 83, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Aladdin
        this.accumulatedOdd = 100;

        this.intervalID = setInterval(() => {
            this.run();
        }, 1000);
        this.tideStatus = 'endTide';
        this.accumulatingFishDeadTime = 0;        
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
                if(temp.fishTypeGroup == 'normal' && vortexFishFormat.find(x => x.id == temp.fishType) != undefined) //doubt
                    isVortexFish = true;
                else if(temp.fishTypeGroup == 'normal' && lightingChainFormat.find(x => x.id == temp.fishType) != undefined) //doubt
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
                    if(fishInfo.fishTypeGroup == 'skill') //doubt
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
                case 2006:
                case 2003:                
                    if(this.scriptPrepareTime > 0)
                        return;
                    //for bg03 and bg06, generate normal fishes
                    this.generateNormalFishes();

                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        //generate skill fish
                        this.generateSkillFish();
                        
                        //generate aladdin boss fish
                        if (!this.checkSpecialTypeFish(83) && (new Date().getTime() - this.accumulatingFishDeadTime) > 50000)
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 83, 'boss'));                    
                        }

                        //generate poseidon or phoenix or thunder dragon
                        if(!this.checkSpecialTypeFish(80) && !this.checkSpecialTypeFish(25) && !this.checkSpecialTypeFish(78))
                        {
                            var rand = getRandomInt(0, 100);
                            if(rand < 40)
                                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 25, 'boss'));
                            else if(rand < 80)
                                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 80, 'boss'));
                            else
                                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 78, 'boss'));
                        }
                    }
                    if(this.scriptTime <= 0)
                        this.scriptPrepareTime = 10;
                    break;
                case 1011:
                    if(this.scriptPrepareTime > 0)
                        return;
                    //generate normal fishes
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        //generate skill fishes
                        this.generateSkillFish();
                        //generate genie boss fish
                        if (!this.checkSpecialTypeFish(82) && getRandomInt(0, 100) < 30 && this.activeSkill != 'sk_RockSkeleton_bomb')
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 82, 'boss'));                    
                        }
                    }
                    break;
                case 1012:
                case 17:                    
                    break;                
                case 2506:
                case 2503:
                    if(this.scriptTime <= 0)
                        this.scriptPrepareTime = 10;
                    break;
            }

            if(this.scriptTime <= 0)
            {   
                this.scriptIndex = (this.scriptIndex+1) % this.scriptList.length;
                var newScript = this.scriptList[this.scriptIndex];
                this.scriptTime = this.scriptPeriods[newScript]; //script time for 1011
                
                this.dispatchScriptEvent(this.scriptName[newScript], newScript);                    
                this.lastScript = this.script;                                
            }

            var curDate = new Date();
            this.fishInfos.forEach(fish => {
                if(fish.birthDate.getTime() + 45000 <  curDate.getTime())
                {
                    this.fishOut(fish.id);
                }
            });
        }
    }

    fishOut(fishId)
    {
        var fishInfo = this.fishInfos.filter(x => x.id == fishId);
        // if(fishInfo != undefined && fishInfo.length > 0 && fishInfo[0].fishType == 83)
        // {
        //     this.accumulatedOdd = fishInfo[0].odds;        
        //     console.log("out aladdin odd: " + this.accumulatedOdd + " id: " + fishInfo[0].id);
        // }
        
        super.fishOut(fishId);
    }
}
module.exports = {GameRoomALD};