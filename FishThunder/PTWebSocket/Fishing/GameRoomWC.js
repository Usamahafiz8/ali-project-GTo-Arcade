const { BulletCounter } = require('./BulletCounter.js');

var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;


class GameRoomWC extends GameRoom
{
    constructor()
    {
        super('gd');
        this.tideStatus = 'startTide';
        this.fishGameType = 'WC';        
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.accumulatedOdd = 0;
        this.accumulatingFishDeadTime = 0;
        this.accumulatingFishOutTime = 0;
        this.betPerOddIncrease = 6;
        /**
         * script indices
         * 2001: BG_01
         * 1001: KING_SPIDERCRAB_ENTER
         * 1002: KING_SPIDERCRAB_LEAVE
         * 17: FISH_LEAVING
         * 2501: BG_01_LEAVE
         * 2007: BG_07
         * 1013: LUCKY_CAT_ENTER
         * 1014: LUCKY_CAT_LEAVE
         * 17: FISH_LEAVING
         * 2507: BG_07_LEAVE
         */
        
        this.scriptList = [2001, 1001, 1002, 17, 2501, 2007, 1013, 1014, 17, 2507];
        this.scriptPeriods = {
            2001: 360,
            1001: 80,
            1002: 2,
            17: 1,
            2501: 2,
            2007: 291,
            1013: 200,
            1014: 1,
            2507: 2
        };
        // this.scriptPeriods = {
        //     2001: 50,
        //     1001: 180,
        //     1002: 2,
        //     17: 1,
        //     2501: 2,
        //     2007: 50,
        //     1013: 80,
        //     1014: 1,
        //     2507: 2
        // };
        
        this.scriptNames = {
            2001: 'BG_01',
            1001: 'KING_SPIDERCRAB_ENTER',
            1002: 'KING_SPIDERCRAB_LEAVE',
            17: 'FISH_LEAVING',
            2501: 'BG_01_LEAVE',
            2007: 'BG_07',
            1013: 'LUCKY_CAT_ENTER',
            1014: 'LUCKY_CAT_LEAVE',
            2507: 'BG_07_LEAVE'
        };

        this.scriptIndex = 0;
        
        this.bossFormat.push({id: 80, fishType: 80, prizeRatio: 2, odds: 100, maxOdds: 1000, fishTypeGroup: 'boss'}); //Phoenix
        this.bossFormat.push({id: 25, fishType: 25, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Mermaid
        this.bossFormat.push({id: 24, fishType: 24, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Crab
        this.bossFormat.push({id: 92, fishType: 92, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Wonder Fortune Cat
        this.bossFormat.push({id: 78, fishType: 78, prizeRatio: 3, odds: 100, maxOdds: 500, fishTypeGroup: 'boss'}); //Thunder dragon        

        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.scriptPrepareTime = 5;
        this.mainScriptStartTime = new Date();
        this.crabOutTime = new Date();
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
                case 2001:
                case 2007:
                    if(this.tideStatus == 'startTide')
                    {
                        this.generateTide();
                    }
                    else if(this.tideStatus == 'tiding' && this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length < 5 && ((new Date()).getTime() - this.mainScriptStartTime.getTime()) >= 20000) //doubt
                    {
                        this.tideStatus = 'endTide';            
                    }
                    if(this.tideStatus == 'endTide')
                        this.generateNormalFishes();
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
                case 1001:
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        if(!this.checkSpecialTypeFish(24) && (new Date().getTime() - this.crabOutTime.getTime()) / 1000 > 20) //generate emperor crab
                        this.sendToAllUsers(this.fishGroupGenerate(2, this.bossFormat, 24, 'boss'));
                    
                        this.generateSkillFish();
                    }                    
                    break;
                case 1013:
                    this.generateNormalFishes();
                    if(this.fishInfos.filter(x => x.fishTypeGroup == 'normal').length > 10)
                    {
                        if(!this.checkSpecialTypeFish(92)) //generate fortune cat
                            this.sendToAllUsers(this.fishGroupGenerate(1, this.bossFormat, 92, 'boss'));
                        
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
                this.dispatchScriptEvent(this.scriptNames[this.scriptIndex], this.scriptList[this.scriptIndex]);
                if(this.scriptList[this.scriptIndex] == 2001 || this.scriptList[this.scriptIndex] == 2007)
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
        var currentOdds = 0;
        if(this.accumulatedOdd != 0)
            currentOdds = 300 + parseInt(this.accumulatedOdd / this.betPerOddIncrease)
        else
            currentOdds = 300;
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

module.exports = {GameRoomWC}