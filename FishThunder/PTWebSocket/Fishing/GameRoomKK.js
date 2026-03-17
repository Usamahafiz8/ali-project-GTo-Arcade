const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;
class GameRoomKK extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'KK';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.betPerOddIncrease = 6;
        
        /**
         * script indices
         * 2005: bg05
         * 1009: buffalo enter
         * 1010: buffalo leave
         * 17: fish fade out
         * 2505: bg05 leave
         * 2006: bg06
         * 1011: kingkong enter
         * 1012: kingkong leave
         * 2506: bg06 leave
         */
        this.scriptList = [2005, 1009, 1010, 17, 2505, 2006, 1011, 1012, 17, 2506];
        this.scriptName = {
            2005: 'BG_05',
            1009: 'BUFFALO_ENTER',
            1010: 'BUFFALO_LEAVE',
            17: 'FISH_LEAVING',
            2505: 'BG_05_LEAVE',            
            2006: 'BG_06',
            1011: 'KINGKONG_ENTER',
            1012: 'KINGKONG_LEAVE',
            2506: 'BG_06_LEAVE'
        }
        this.scriptPeriods = {
            2005: 294,
            1009: 200,
            1010: 2,
            17: 2,
            2505: 2,            
            2006: 294,
            1011: 200,
            1012: 2,
            2506: 2
        };
        
        this.scriptIndex = 0;        
        this.lastScript = this.scriptList[this.scriptIndex];
        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.bossFormat.push({id: 80, fishType: 80, prizeRatio: 5, odds: 100, maxOdds: 1000, fishTypeGroup: 'boss'}); //Phoenix
        this.bossFormat.push({id: 25, fishType: 25, prizeRatio: 5, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Poseidon
        this.bossFormat.push({id: 78, fishType: 78, prizeRatio: 5, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Thunder dragon
        this.bossFormat.push({id: 82, fishType: 82, prizeRatio: 5, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //KingKong
        this.bossFormat.push({id: 81, fishType: 81, prizeRatio: 5, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Frenzied fury        
        this.accumulatedOdd = 0;

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
                case 2006:
                case 2005:                
                    if(this.scriptPrepareTime > 0)
                        return;
                    //for bg05 and bg06, generate normal fishes
                    this.generateNormalFishes();

                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        //generate skill fish
                        this.generateSkillFish();

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

                    //check script time over
                    if(this.scriptTime <= 0)
                    {   
                        this.scriptIndex = (this.scriptIndex+1) % this.scriptList.length;
                        this.scriptTime = 200; //script time for 1011
                        var newScript = this.scriptList[this.scriptIndex];
                        this.dispatchScriptEvent(this.scriptName[newScript], newScript);                    
                        this.lastScript = this.script;
                        this.scriptPrepareTime = 10;
                    }
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
                        //generate skeleton fish
                        if (!this.checkSpecialTypeFish(82) && getRandomInt(0, 100) < 30 && this.activeSkill != 'sk_KingKong_bomb')
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 82, 'boss'));                    
                        }
                    }
                    break;
                case 1009:
                    if(this.scriptPrepareTime > 0)
                        return;
                    //generate normal fishes
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        //generate skill fishes
                        this.generateSkillFish();
                        //generate skeleton fish
                        if (!this.checkSpecialTypeFish(81) && getRandomInt(0, 100) < 30 && this.activeSkill != 'sk_buffalo_bomb')
                        {
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 81, 'boss'));
                        }
                    }
                    break;
                case 1012:
                case 17:                   
                case 2506:
                case 2503:
                case 1006:
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
                if(this.scriptList[this.scriptIndex] == 2003 || this.scriptList[this.scriptIndex] == 2006)
                {
                    this.mainScriptStartTime = new Date();
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
module.exports = {GameRoomKK};