const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;
class GameRoomTD extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'TD';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.betPerOddIncrease = 6;        
        this.isCrab = true;
        
        this.scriptList = [2001, 1001, 1002, 17, 2501, 2002, 1003, 1004, 17, 2502, 2003, 1005, 1006, 17, 2503, 2004, 1007, 1008, 17, 2504];
        this.scriptName = {
            2001: 'BG_01',
            1001: 'KING_SPIDERCRAB_ENTER',
            1002: 'KING_SPIDERCRAB_LEAVE',
            17: 'FISH_LEAVING',
            2501: 'BG_01_LEAVE',
            2002: 'BG_02',
            1003: 'KING_TACO_ENTER',
            1004: 'KING_TACO_LEAVE',
            2502: 'BG_02_LEAVE',
            2003: 'BG_03',
            1005: 'KING_LACOSTE_ENTER',
            1006: 'KING_LACOSTE_LEAVE',
            17: 'FISH_LEAVING',
            2503: 'BG_03_LEAVE',
            2004: 'BG_04',
            1007: 'KING_ANGLER_ENTER',
            1008: 'KING_ANGLER_LEAVE',
            17: 'FISH_LEAVING',
            2504: 'BG_04_LEAVE'
        }
        this.scriptPeriods = {
            2001: 364,
            1001: 80,
            1002: 1,
            17: 3,
            2501: 2,
            2002: 364, 
            1003: 80, 
            1004: 1,
            2502: 2,
            2003: 364, 
            1005: 80,
            1006: 1,        
            2503: 2,
            2004: 364,
            1007: 80,
            1008: 1,           
            2504: 2
        };
        
        
        this.scriptIndex = 0;        
        this.lastScript = this.scriptList[this.scriptIndex];
        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.bossFormat.push({id: 20, fishType: 20, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Crocodile
        this.bossFormat.push({id: 35, fishType: 35, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Darkness monster
        this.bossFormat.push({id: 61, fishType: 61, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus1
        this.bossFormat.push({id: 62, fishType: 62, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus2
        this.bossFormat.push({id: 63, fishType: 63, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus3
        this.bossFormat.push({id: 64, fishType: 64, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus4
        this.bossFormat.push({id: 65, fishType: 65, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus5
        this.bossFormat.push({id: 66, fishType: 66, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus6
        this.bossFormat.push({id: 24, fishType: 24, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //EmperorCrab
        this.bossFormat.push({id: 78, fishType: 78, prizeRatio: 2, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Thunder Dragon
        this.bossFormat.push({id: 87, fishType: 87, prizeRatio: 2, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Freeze Dragon
        this.bossFormat.push({id: 88, fishType: 88, prizeRatio: 2, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Flare Dragon
        this.bossFormat.push({id: 32, fishType: 32, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Dragon TURTLE
        this.bossFormat.push({id: 19, fishType: 19, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Fire Dragon
        this.bossFormat.push({id: 29, fishType: 29, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //TreasureCrab
        this.accumulatedOdd = 0;
        this.mainScriptStartTime = new Date();
        this.intervalID = setInterval(() => {            
            this.run();
        }, 1000);

        this.treasureInitScore = 50000;
        this.treasureLevelScore = 5000;
        this.treasureScore = this.treasureInitScore;
        this.treasureMaxScore = 300000;
        this.treasureLevel = 0;
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

    generateOctopus()
    {
        var is61Oct = this.checkSpecialTypeFish(61);
        var is62Oct = this.checkSpecialTypeFish(62);
        var is63Oct = this.checkSpecialTypeFish(63);
        var is64Oct = this.checkSpecialTypeFish(64);
        var is65Oct = this.checkSpecialTypeFish(65);
        var is66Oct = this.checkSpecialTypeFish(66);
        var octExistNum = 0;
        var tempRandom = [61, 62, 63, 64, 65, 66];
        if(is61Oct)
        {
            octExistNum++;
            tempRandom.splice(tempRandom.findIndex(x => x == 61), 1);
        }
        if(is62Oct)
        {
            octExistNum++;
            tempRandom.splice(tempRandom.findIndex(x => x == 62), 1);
        }
        if(is63Oct)
        {
            octExistNum++;
            tempRandom.splice(tempRandom.findIndex(x => x == 63), 1);
        }
        if(is64Oct)
        {
            octExistNum++;
            tempRandom.splice(tempRandom.findIndex(x => x == 64), 1);
        }
        if(is65Oct)
        {
            octExistNum++;
            tempRandom.splice(tempRandom.findIndex(x => x == 65), 1);
        }
        if(is66Oct)
        {
            octExistNum++;
            tempRandom.splice(tempRandom.findIndex(x => x == 66), 1);
        }
        if(octExistNum < 2)
        {
            if(octExistNum == 0)
            {
                var randomIndex = getRandomInt(0, tempRandom.length);
                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, tempRandom[randomIndex], 'boss'));
                tempRandom.splice(randomIndex, 1);
                randomIndex = getRandomInt(0, tempRandom.length);
                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, tempRandom[randomIndex], 'boss'));
            }
            else if(octExistNum == 1)
            {
                var randomIndex = getRandomInt(0, tempRandom.length);
                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, tempRandom[randomIndex], 'boss'));
            }
        }
    }

    generateTreasureCrab()
    {
        if(!this.checkSpecialTypeFish(29) && getRandomInt(0, 100) < 25 && this.isCrab)
        {
            var crab = this.fishGroupGenerate(1, this.bossFormat, 29, 'boss');
            crab['data']['creat_timestamp'] = parseInt((new Date().getTime() - 100000)/1000);
            crab['data']['timestamp'] = parseInt(new Date().getTime()/1000);
            crab['data']['treasure_level'] = this.treasureLevel; // 0,1,2
            crab['data']['treasure_score'] = this.treasureScore;
            this.sendToAllUsers(crab);
        }
    }

    generateDragons()
    {
        if(!this.checkSpecialTypeFish(88) &&!this.checkSpecialTypeFish(78) && (this.activeSkill != 'sk_RedDragon_bomb' && this.activeSkill != 'sk_26' ) && getRandomInt(0, 100) < 30)
        {
            if(getRandomInt(0, 100) < 90)
                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 88, 'boss'));
            else
                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 78, 'boss'));
        }
            

        if(!this.checkSpecialTypeFish(87) && !this.checkSpecialTypeFish(19) && getRandomInt(0, 100) < 50)
        {
            if(getRandomInt(0, 100) < 50)
                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 87, 'boss'));
            else
                this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 19, 'boss'));
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
                case 2001:
                case 2002:
                case 2003:
                case 2004:
                    if(this.scriptPrepareTime > 0)
                        return;          
                    //for bg03 and bg06, generate normal fishes
                    if(this.tideStatus == 'startTide' && this.fishInfos.length <= 3)
                    {
                        this.generateTide();
                    }
                    else if(this.tideStatus == 'tiding' && this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length < 10 && ((new Date()).getTime() - this.mainScriptStartTime.getTime()) >= 20000)
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
                        this.generateTreasureCrab();
                        //generate dragons & turtle generate
                        if (this.scriptTime > 20)
                        {
                            this.generateDragons();
                        }
                        if(!this.checkSpecialTypeFish(32) && getRandomInt(0,100) < 30) //generate turtle dragon
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 32, 'boss'));
                    }
                    break;
                case 1001:
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        if(!this.checkSpecialTypeFish(24) && (new Date().getTime() - this.crabOutTime.getTime()) / 1000 > 20) //generate emperor crab
                            this.sendToAllUsers(this.fishGroupGenerate(2, this.bossFormat, 24, 'boss'));
                        this.generateSkillFish();
                    }
                    break;
                case 1003:
                    //octopus enter
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        this.generateOctopus();
                        this.generateSkillFish();
                    }
                    break;
                case 1005:
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        if(!this.checkSpecialTypeFish(20)) //generate ancient crocodile
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 20, 'boss'));
                        this.generateSkillFish();
                    }
                    break;
                case 1007:
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        if(!this.checkSpecialTypeFish(35)) //generate darkness monster
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 35, 'boss'));
                        this.generateSkillFish();
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
module.exports = {GameRoomTD};