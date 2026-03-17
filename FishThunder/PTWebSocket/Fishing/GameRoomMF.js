const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;

class GameRoomMF extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'MF';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.accumulatedOdd = 0;
        this.godWealthOdd = 300;
        this.accumulatingFishDeadTime = 0;
        this.accumulatingFishOutTime = 0;
        this.betPerOddIncrease = 1;
        this.darknessMonsterKillTime = new Date();
        /**
         * script indices
         * 2002: BG_02
         * 1003: KING_LACO_ENTER
         * 1004: KING_LAGO_LEAVE
         * 17: FISH_LEAVING
         * 2502: BG_02_LEAVE
         * 2004: BG_04
         * 1007: KING_ANGLER_ENTER
         * 1008: KING_ANGLER_LEAVE
         * 17: FISH_LEAVING
         * 2504: BG_04_LEAVE
         */
        
        this.scriptList = [2004, 1007, 1008, 17, 2504, 2002, 1003, 1004, 17, 2502];
        this.scriptPeriods = {
            2002: 251,
            1003: 94,
            1004: 1,
            17: 3,
            2502: 2,
            2004: 291,
            1007: 94,
            1008: 1,
            2504: 3
        };
        
        this.scriptNames = {
            2002: 'BG_02',
            1003: 'KING_TACO_ENTER',
            1004: 'KING_TACO_LEAVE',
            17: 'FISH_LEAVING',
            2502: 'BG_02_LEAVE',
            2004: 'BG_04',
            1007: 'KING_ANGLER_ENTER',
            1008: 'KING_ANGLER_LEAVE',
            2504: 'BG_04_LEAVE'
        };

        this.scriptIndex = 0;
        
        this.bossFormat.push({id: 80, fishType: 80, prizeRatio: 10, odds: 100, maxOdds: 1000, fishTypeGroup: 'boss'}); //Phoenix
        this.bossFormat.push({id: 25, fishType: 25, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Mermaid
        this.bossFormat.push({id: 35, fishType: 35, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Darkness monster
        this.bossFormat.push({id: 78, fishType: 78, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Thunder dragon
        this.bossFormat.push({id: 61, fishType: 61, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus1
        this.bossFormat.push({id: 62, fishType: 62, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus2
        this.bossFormat.push({id: 63, fishType: 63, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus3
        this.bossFormat.push({id: 64, fishType: 64, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus4
        this.bossFormat.push({id: 65, fishType: 65, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus5
        this.bossFormat.push({id: 66, fishType: 66, prizeRatio: 10, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //octopus6
        this.bossFormat.push({id: 99999, fishType: 116, prizeRatio: 3, odds: 300, maxOdds: 2000, fishTypeGroup: 'boss'}); //God of wealth

        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.scriptPrepareTime = 5;
        this.mainScriptStartTime = new Date();
        this.intervalID = setInterval(() => {
            this.run();
        }, 1000);        
    }

    getOddsSet()
    {
        var currentOdds = getRandomInt(300, 1500);
        var fish7Reply = {
            cmd: 'f7',
            sys: 'fish',
            data: {
                fish_id: 99999,
                odds: currentOdds
            }
        };
        if(currentOdds >= 300 && currentOdds <= 400)
            fish7Reply.data.level = 1;
        else if(currentOdds > 400 && currentOdds <= 600)        
            fish7Reply.data.level = 2;
        else if(currentOdds > 600 && currentOdds <= 700)        
            fish7Reply.data.level = 3;
        else if(currentOdds > 700 && currentOdds <= 900)
            fish7Reply.data.level = 4;
        else if(currentOdds > 900 && currentOdds <= 1400)
            fish7Reply.data.level = 5;
        else if(currentOdds > 1400 && currentOdds <= 2000)
            fish7Reply.data.level = 6;
        else
        {
            currentOdds = 2000;
            fish7Reply.data.level = 6;
        }
        fish7Reply.data.timestamp = new Date().getTime();
        var fishInfo = this.fishInfos.find(x => x.id == 99999);
        if(fishInfo != undefined)
        {
            fishInfo.odds = currentOdds;
            fishInfo.maxOdds = currentOdds;
            this.sendToAllUsers(fish7Reply);
        }
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

    generateDarknessMonster()
    {
        if(!this.checkSpecialTypeFish(35) && (new Date().getTime() - this.darknessMonsterKillTime.getTime()) / 1000 > 5)
            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 35, 'boss'));
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

    generateWealthGod()
    {
        if(!this.checkSpecialTypeFish(116) && ((new Date()).getTime() - this.accumulatingFishDeadTime) / 1000 > 30 && (new Date().getTime() - this.accumulatingFishOutTime) / 1000 > 15)
        {
            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 116, 'boss'));
            this.godOddsSet();
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
            if(this.scriptPrepareTime > 0)
                return;
            switch(script)
            {
                case 2002:
                case 2004:
                    //bg02, 04
                    if(this.tideStatus == 'startTide')
                    {
                        this.generateTide();
                    }
                    else if(this.tideStatus == 'tiding' && this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length < 35 && ((new Date()).getTime() - this.mainScriptStartTime.getTime()) >= 20000) //doubt
                    {
                        this.tideStatus = 'endTide';            
                    }
                    if(this.tideStatus == 'endTide')
                        this.generateNormalFishes();
                    this.generateWealthGod();

                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
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
                    break;
                case 1003:
                    //octopus enter
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        this.generateOctopus();
                        this.generateSkillFish();
                    }
                    
                    this.generateWealthGod();                    
                    break;
                case 1007:
                    //darkness monster enter
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        this.generateDarknessMonster();
                        this.generateSkillFish();
                    }
                    
                    this.generateWealthGod();                    
                    break;
                case 1004:
                case 1008:                    
                case 17:
                case 2502:
                case 2504:
                    break;
            }

            if(this.scriptTime > 3 && (script == 17 || script == 1004 || script == 1008))
                this.scriptTime = 0;
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
                this.dispatchScriptEvent(this.scriptNames[this.scriptIndex], this.scriptList[this.scriptIndex]);
                if(this.scriptList[this.scriptIndex] == 2002 || this.scriptList[this.scriptIndex] == 2004)
                {
                    this.mainScriptStartTime = new Date();
                    this.tideStatus = 'startTide';
                    this.scriptPrepareTime = 5;
                }
            }
        }
    }    

    godOddsSet()
    {
        var currentOdds = this.godWealthOdd;        

        var fish7Reply = { cmd: 'f7', sys: 'fish', data: {fish_id: 99999, odds: currentOdds} };
        if(currentOdds >= 300 && currentOdds <= 400)
            fish7Reply.data.level = 1;
        else if(currentOdds > 400 && currentOdds <= 600)
            fish7Reply.data.level = 2;        
        else if(currentOdds > 600 && currentOdds <= 800)
            fish7Reply.data.level = 3;
        else if(currentOdds > 700 && currentOdds <= 900)
            fish7Reply.data.level = 4;
        else if(currentOdds > 900 && currentOdds <= 1400)
            fish7Reply.data.level = 5;
        else if(currentOdds > 1400 && currentOdds <= 2000)
            fish7Reply.data.level = 6;
        else if(currentOdds > 2000)
        {
            fish7Reply.data.level = 6;
            currentOdds = 2000;
        }
        fish7Reply.data.timestamp = new Date().getTime();
        var fishInfo = this.fishInfos.find(x => x.id == 99999)
        {
            fishInfo.odds = currentOdds;
            fishInfo.maxOdds = currentOdds;
            this.sendToAllUsers(fish7Reply);
        }
    }

    fishOut(fishId)
    {
        if(fishId == 99999)
        {
            var fishInfo = this.fishInfos.filter(x => x.id == fishId);
            if(fishInfo != undefined && fishInfo[0] != undefined)
            {
                this.accumulatingFishOutTime = new Date().getTime();
            }
        }
        
        // console.log("before out fish count: " + this.fishInfos.length);
        super.fishOut(fishId);
        // console.log("out fish: " + fishId + ", fish count: " + this.fishInfos.length);
    }
}

module.exports = {GameRoomMF}