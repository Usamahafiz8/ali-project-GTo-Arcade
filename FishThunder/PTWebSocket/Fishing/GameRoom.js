var fs = require('fs');
var Vec2 = require('vec2');
const { BulletCounter } = require('./BulletCounter');
var sendWSMessage = require('./Network').sendWSMessage;
var global = require('./Global.js');
// var gFishMaxSize = require('./Global').gFishMaxSize;
// var gAllFishInfo = require('./Global').gAllFishInfo;
// var normalFishFormat = require('./Global').normalFishFormat;
// var skillFishFormat = require('./Global').skillFishFormat;
// var vortexFishFormat = require('./Global').vortexFishFormat;
// var lightingChainFormat = require('./Global').lightingChainFormat;
// var samplePoints = require('./Global').samplePoints;
var gTides = require('./Global').gTides;
var getRandomInt = require('./utils.js').getRandomInt;

class GameRoom{    
    constructor(gameType)
    {
        this.isAvailable = false;
        this.clients = [];
        this.fishInfos = [];
        this.armyInfo = [];        
        this.scriptList = [];
        this.scriptIndex = 0;
        this.scriptTime = 300;
        this.scriptPrepareTime = 10;
        this.scriptExtendedCount = 0;
        this.lastScript;
        this.bossFormat = [];
        this.roomStartDate = new Date();
        this.tideStatus = 'startTide';
        this.currentFishIndex = 100;        
        this.fishGameType = 'MF';        
        this.allowGenerateFish = false;
        this.splashDate = new Date();
        this.lastTide = new Date();
        this.maxRoomNormalOdds = 350;
        this.bossFishLimited = 10;        
        this.tideMaxTime = 0;
        this.activeSkill = '';
        this.redisSyncTime = 0;
        this.redisSyncUnit = 60;
        this.syncTime = new Date();
        this.shop_id = 0;
        this.gameType = gameType;
        this.roomType = 0;
        this.hitOnAccmulatedBoss = 0;
        if (this.gameType == 'gf')
        {
            this.seats = [0,0,0,0,0,0];
        }
        else
        {
            this.seats = [0,0,0,0];
        }
    }    

    addClient(player)
    {
        this.clients.push(player);

        //assign seat id
        var totalPlayers = this.seats.reduce((a, b) => {return a + b});
        if(totalPlayers == (player.gameType == 'gf' ? 6 : 4))
        {
            player.seatId = -1;
        }
        else
        {
            var availableSeats = [];
            for(var i = 0; i < this.seats.length; i++)
            {
                if(this.seats[i] == 0)
                    availableSeats.push(i);
            }
            var newSeat = availableSeats[Math.floor(Math.random()*availableSeats.length)];
            this.seats[newSeat] = 1;
            player.seatId = newSeat;
        }
        redisBridge.synchronizePlayerToDB(player);
        playerOnline++;
        console.log('Players online: ' + playerOnline);
    }

    removeClient(player)
    {
        for(var i = 0; i < this.clients.length; i++)
        {
            if(this.clients[i] == player)
            {
                this.clients.splice(i, 1);
                this.seats[player.seatId] = 0;
                break;
            }
        }
        if (player.gameType == 'gd')
        {
            var leaveRoom = {
                sys: 'game',
                cmd: 'leave_table',
                data: {
                    seat: player.seatId
                }
            };
            player.sendToOtherUsers(leaveRoom);
        }
        
        player = null;
        playerOnline--;
        console.log('Players online: ' + playerOnline);
    }

    sendToAllUsers(data)
    {
        this.clients.forEach(player => {
            if(player.initialized)
                sendWSMessage(player.socket, data);
        });
    }

    getSceneId()
    {
        return this.scriptList[this.scriptIndex];
    }

    getBigSceneId()
    {
        var bgId = this.getSceneId();
        if (bgId < 2000)
        {
            var tempIndex = this.scriptIndex;
            while(this.scriptList[tempIndex] < 2000)
            {
                tempIndex--;
            }
            bgId = this.scriptList[tempIndex];
        }
        return bgId;
    }

    getCurrentFrame()
    {
        var current = new Date();        
        return parseInt((current.getTime() - this.roomStartDate.getTime()) / 1000);
    }

    checkSpecialTypeFish(fishType)
    {
        var isAvailable = false;
        try{
            this.fishInfos.forEach(fishInfo => {
                if (this.gameType == 'gd')
                {
                    if(fishInfo.fishType == fishType)
                    {
                        isAvailable = true;
                        throw 'Break';
                    }
                }else if (this.gameType == 'gf')
                {
                    if(fishInfo.fishId == fishType)
                    {
                        isAvailable = true;
                        throw 'Break';
                    }
                }
            });
        }catch(e)
        {
            if (e !== 'Break') throw e
        }
        return isAvailable;
    }

    checkSpecialTypeFishLen(fishType)
    {
        return this.fishInfos.filter(x => x.fishId == fishType).length;
    }

    checkSpecialClassFish(classId)
    {
        var isAvailable = false;
        try{
            this.fishInfos.forEach(fishInfo => {
                if(fishInfo.classId == classId)
                {
                    isAvailable = true;
                    throw 'Break';
                }
            });
        }catch(e)
        {
            if (e !== 'Break') throw e
        }
        return isAvailable;
    }

    generateFishId()
    {
        if(this.currentFishIndex > 5000)
            this.currentFishIndex = 100;
        else 
            this.currentFishIndex++;
        return this.currentFishIndex;
    }

    changeBackground(id)
    {
        var response = {
            cmd: 'f6',
            sys: 'fish',
            data: {
                timestamp: parseInt(new Date().getTime()/1000)
            }
        };
        response.data[1] = 'Background'; // id
        response.data[2] = id; // back_id
        this.scriptList[this.scriptIndex] = id;
        this.sendToAllUsers(response);        
    }

    dispatchScriptEvent(name, id)
    {
        if (this.gameType == 'gd')
        {
            var response = {
                cmd: 'f6',
                sys: 'fish',
                data: {
                    timestamp: parseInt(new Date().getTime()/1000)
                }
            };
            response.data[1] = name;
            response.data[2] = id; // back_id        
            this.sendToAllUsers(response);
        } else if (this.gameType == 'gf')
        {
            var period = name;
            var changeRoom = {
                message:{
                    sceneid: id,
                    btime: new Date().getTime(),
                    etime: new Date().getTime() + period * 1000
                },
                succ: true,
                errinfo: 'ok',
                type: 'changescene'
            };
            this.clients.forEach(player => {
                sendWSMessage(player.socket, changeRoom);
            });
        }
        this.scriptExtendedCount = 0;
    }

    getTotalNormalOdds()
    {
        var totalOdds = 0;
        this.fishInfos.forEach(fishInfo => {
            if(fishInfo.fishTypeGroup == 'normal') //doubt
            {
                if(fishInfo.odds != fishInfo.maxOdds)
                {
                    if(fishInfo.odds > fishInfo.maxOdds)
                        totalOdds += fishInfo.odds;
                    else
                        totalOdds += (fishInfo.odds + fishInfo.maxOdds) / 2;
                }
            }
        });
        return totalOdds;
    }

    getTotalSelectedNormalOdds(limit)
    {
        var totalOdds = 0;
        this.fishInfos.forEach(fishInfo => {
            if(fishInfo.fishTypeGroup == 'normal' && fishInfo.fishType < limit) //doubt
            {
                if(fishInfo.odds != fishInfo.maxOdds)
                {
                    if(fishInfo.odds > fishInfo.maxOdds)
                        totalOdds += fishInfo.odds;
                    else
                        totalOdds += (fishInfo.odds + fishInfo.maxOdds) / 2;
                }
            }
        });
        return totalOdds;
    }


    fishArryGenerateGroupDetail(classid,fishid,born_time,routeid,offsettype,offsetx,offsety,offsetr,rate,ext,fishTypeGroup)
    {
        var fishId = this.generateFishId();
        var route = global.gRoutes.find(x => x.id == routeid);
        
        var fishInfo = {
            id: fishId,
            fishId: fishid,
            classId: classid,
            fishType: fishid,
            routeId: routeid,
            bornTime: born_time,
            deadTime: born_time + route.alivetime,
            offsetType: offsettype,
            offsetX: offsetx,
            offsetY: offsety,
            offsetR: offsetr,
            rate: rate,
            ext: ext,
            fishTypeGroup: fishTypeGroup,
            odds: rate,
            maxOdds: rate,
        }
        this.fishInfos.push(fishInfo);
        return {id:fishId,classid: classid,fishid: fishid,born_time: born_time,routeid: routeid,dead_time: fishInfo.deadTime,offsettype: offsettype,offsetx: offsetx,
            offsety: offsety,offsetr: offsetr,rate: rate,ext: ext
        };
    }

    fishArryGenerateGroupDetail(classid,fishid,born_time,routeid,offsettype,offsetx,offsety,offsetr,rate,ext,fishTypeGroup,dead_time)
    {
        var fishId = this.generateFishId();
        
        var fishInfo = {
            id: fishId,
            fishId: fishid,
            classId: classid,
            fishType: fishid,
            routeId: routeid,
            bornTime: born_time,
            deadTime: dead_time,
            offsetType: offsettype,
            offsetX: offsetx,
            offsetY: offsety,
            offsetR: offsetr,
            rate: rate,
            ext: ext,
            fishTypeGroup: fishTypeGroup,
            odds: rate,
            maxOdds: rate,
        }
        this.fishInfos.push(fishInfo);
        return {id:fishId,classid: classid,fishid: fishid,born_time: born_time,routeid: routeid,dead_time: fishInfo.deadTime,offsettype: offsettype,offsetx: offsetx,
            offsety: offsety,offsetr: offsetr,rate: rate,ext: ext
        };
    }

    fishArryGenerateGroup(fishid,routeId,fishTypeGroup,bornTime,offsetX,offsetY)
    {
        if (fishid == 20 || fishid == 26)
        {
            routeId = 101;
        }
        var fishId = this.generateFishId();
        var route = global.gRoutes.find(x => x.id == routeId);
        var fishOdd = global.gAllFishInfo.find(x => x.fishType == fishid);
        if (fishOdd == null)
        {
            fishOdd = {
                odds:1000, 
                maxOdds: 1000
            };
        }
        var fishInfo = {
            id: fishId,
            fishId: fishid,
            classId: fishid,
            fishType: fishid,
            routeId: routeId,
            bornTime: bornTime,
            deadTime: bornTime + route.alivetime,
            offsetType: 0,
            offsetX: offsetX,
            offsetY: offsetY,
            offsetR: 0,
            rate: fishOdd.odds ,
            ext: 0,
            fishTypeGroup: fishTypeGroup,
            odds: fishOdd.odds,
            maxOdds: fishOdd.maxOdds
        };
        this.fishInfos.push(fishInfo);
        return {id:fishId,classid: fishid,fishid: fishid,born_time: bornTime,routeid: routeId,dead_time: fishInfo.deadTime,offsettype: fishInfo.offsetType,offsetx: fishInfo.offsetX,
            offsety: fishInfo.offsetY,offsetr: fishInfo.offsetR,rate: fishInfo.maxOdds,ext: fishInfo.ext
        };
    }

    fishArryGenerateWithType(fishid,routeId,fishTypeGroup,classid,fishOddInfo,additive)
    {
        var fishId = this.generateFishId();
        var curTime = new Date().getTime();
        var route = global.gRoutes.find(x => x.id == routeId);
        var fishOdd = fishOddInfo.find(x => x.fishType == fishid);
        if (fishOdd == null)
        {
            fishOdd = {
                odds:10, 
                maxOdds: 10
            };
        }
        
        var odds = getRandomInt(fishOdd.odds,fishOdd.maxOdds);
        var fishInfo = {
            id: fishId,
            fishId: fishid,
            classId: classid,
            fishType: fishid + additive,
            routeId: routeId,
            bornTime: curTime,
            deadTime: curTime + route.alivetime,
            offsetType: 0,
            offsetX: 0,
            offsetY: 0,
            offsetR: 0,
            rate: odds ,
            ext: 0,
            fishTypeGroup: fishTypeGroup,
            odds: odds,
            maxOdds: odds,
        };
        this.fishInfos.push(fishInfo);
        return {id:fishId,classid: classid ,fishid: fishid,born_time: curTime,routeid: routeId,dead_time: fishInfo.deadTime,offsettype: fishInfo.offsetType,offsetx: fishInfo.offsetX,
            offsety: fishInfo.offsetY,offsetr: fishInfo.offsetR,rate: fishInfo.maxOdds,ext: fishInfo.ext
        };
    }

    fishArryGenerate(fishid,routeId,fishTypeGroup)
    {
        if (fishid == 20 || fishid == 26)
        {
            routeId = 101;
        } else if (fishid == 21){
            routeId = 110;
        }  else if (fishid == 35)
        {
            routeId = getRandomInt(0,100) < 50 ? 327: 328;
        } 
        
        var fishId = this.generateFishId();
        var curTime = new Date().getTime();
        var route = global.gRoutes.find(x => x.id == routeId);
        var fishOdd = global.gAllFishInfo.find(x => x.fishType == fishid);
        if (fishOdd == null)
        {
            fishOdd = {
                odds:1000, 
                maxOdds: 1000
            };
        }
        var fishInfo = {
            id: fishId,
            fishId: fishid,
            classId: fishid,
            fishType: fishid,
            routeId: routeId,
            bornTime: curTime,
            deadTime: curTime + route.alivetime,
            offsetType: 0,
            offsetX: 0,
            offsetY: 0,
            offsetR: 0,
            rate: fishOdd.odds ,
            ext: 0,
            fishTypeGroup: fishTypeGroup,
            odds: fishOdd.odds,
            maxOdds: fishOdd.maxOdds
        };
        this.fishInfos.push(fishInfo);
        return {id:fishId,classid: fishid ,fishid: fishid,born_time: curTime,routeid: routeId,dead_time: fishInfo.deadTime,offsettype: fishInfo.offsetType,offsetx: fishInfo.offsetX,
            offsety: fishInfo.offsetY,offsetr: fishInfo.offsetR,rate: fishInfo.maxOdds,ext: fishInfo.ext
        };
    }

    fishGroupGenerate(fishCnt, fishTypeInfo, type, fishTypeGroup)
    {
        var fishReply = {
            cmd: 'f1',
            sys: 'fish',
            data: {
                timestamp: parseInt(new Date().getTime()/1000),                
            }
        }
        fishReply.data[15] = []; //params
        fishReply.data[11] = []; //fishgroup
        fishReply.data[10] = "11";
        fishReply.data[14] = this.getSceneId(); //scene

        for(var i = 0; i < fishCnt; i++)
        {
            var fishType = fishTypeInfo[getRandomInt(0, fishTypeInfo.length)].fishType;
            while(fishType >= 15 && fishType <= 18 ) //exclude large fish
            {
                fishType = fishTypeInfo[getRandomInt(0, fishTypeInfo.length)].fishType;
            }
            var fishInfo = {};
            fishInfo[1] = this.generateFishId(); //id
            fishInfo[9] = this.getCurrentFrame(); //frame
            fishInfo[6] = 0; //feature
            fishInfo[16] = null; //king status
            fishInfo[7] = 0; //position
            
            if(type != -1)
            {
                fishType = type;
                fishInfo[2] = fishType; //type

                var isValid = false;
                while(!isValid)
                {
                    fishInfo[3] = 34; //x
                    fishInfo[4] = getRandomInt(0, 150);
                    if(getRandomInt(0, 100) < 50)
                        fishInfo[5] = getRandomInt(270, 360);
                    else
                        fishInfo[5] = getRandomInt(0, 90);
                    isValid = this.isValidPosition(fishInfo[3], fishInfo[4], fishInfo[5], fishType);
                }

                if(fishType == 15)
                {
                    fishReply.data[15].push({'inflated':[getRandomInt(3, 8), getRandomInt(3, 8), getRandomInt(3, 8), getRandomInt(3, 8), getRandomInt(3, 8)]});
                }
                else if(fishType == 24)
                {
                    var paramGroup = null;
                    if(i == 0)
                    {
                        paramGroup = {turning:[0,1,1,1,1],speed:[2.6, 2.4, 2.2, 3.6, 4],init:0};
                    }
                    else
                    {
                        paramGroup = {turning:[0,1,1,1,1],speed:[2.1, 2.7, 1.3, 3.1, 1.5],init:1};
                    }
                    fishReply.data[15].push(paramGroup);
                }
                else if(fishType == 116) //MF Wealth God
                {
                    fishInfo[1] = 99999; //id
                }
                else if(fishType == 82)
                { //genie of ALD
                    var pos = [{x: 40, y:1, o:316}, {x: 79, y:14, o:264}, {x: 6, y:3, o:204}, {x: 77, y:67, o:251}, {x: 89, y:7, o:115}, {x: 49, y:77, o:79}, {x: 69, y:59, o:288}, {x: 78, y:28, o:234}, {x: 20, y:36, o:339}, {x: 3, y:23, o:344}, {x: 4, y:15, o:285}, {x: 43, y:67, o:154}];
                    var randPos = pos[getRandomInt(0, pos.length)];
                    fishInfo[3] = randPos.x;
                    fishInfo[4] = randPos.y;
                    fishInfo[5] = randPos.o;
                    fishInfo[6] = 201;
                    fishReply.data[14] = this.lastScript; 
                }
                else if(fishType == 83) //ALD aladdin, ZB Grave
                {
                    fishInfo[6] = 202;
                    fishReply.data.Vampire_odds = this.accumulatedOdd;
                }
                else if(fishType == 84) //MTY LuckyBuddha
                {
                    fishInfo[6] = 203;
                    fishReply.data.create_timestamp = new Date().getTime()/1000;
                    fishReply.data.LuckyBuddha_odds = this.accumulatedOdd;
                    
                    var samplePoints = [[34,137,75],[34,6,335],[34,134,52],[34,129,13],[34,132,339],[34,65,3],[34,51,285],[34,66,299],[34,107,323],[34,68,299],[34,103,77],[34,110,284],[34,95,63],[34,87,60],[34,102,34],[34,121,359],[34,61,36],[34,36,57],[34,11,283],[34,14,289],[34,14,277],[34,55,23],[34,52,300],[34,40,335],[34,94,19],[34,8,24],[34,81,30]];
                    var sampleIndex = getRandomInt(0,samplePoints.length - 1);
                    fishInfo[3] = samplePoints[sampleIndex][0];
                    fishInfo[4] = samplePoints[sampleIndex][1];
                    fishInfo[5] = samplePoints[sampleIndex][2];
                    var paramGroup = {"turning":[0.0,1.0,1.0,0.0,0.0]};
                    fishReply.data[15].push({});
                    fishReply.data[15].push({});
                    fishReply.data[15].push({});
                    fishReply.data[15].push(paramGroup)
                    fishReply.data[15].push({});
                    fishReply.data[15].push({});
                    fishReply.data[15].push({});
                    fishReply.data[15].push({});
                    // console.log('in aladdin odd: ' + this.accumulatedOdd + " id: " + fishInfo[1]);
                }
                else if(fishType == 93) //FishCircus clown
                {
                    fishInfo[6] = 206;
                    fishReply.data.CircusClown_odds = this.accumulatedOdd;
                }
                else if(fishType == 111 || fishType == 115 || fishType == 113)
                {
                    fishInfo[6] = 206;
                    fishReply.data.create_timestamp = new Date().getTime() / 1000;
                }
                else if(fishType == 114) //FishKingOfCrab Lobster
                {
                    fishInfo[6] = 207;
                    fishReply.data.upgrade_fish = {
                        fish: fishInfo[1],
                        level: this.bulletCounter.lobsterInfo.cur_level,
                        size: this.bulletCounter.lobsterInfo.cur_size
                    };
                }
            }
            else
            {
                fishInfo[2] = fishType; //type
                
                var isValid = false;
                while(!isValid)
                {
                    fishInfo[3] = 34; //x
                    fishInfo[4] = getRandomInt(0, 150);
                    if(getRandomInt(0, 100) < 50)
                        fishInfo[5] = getRandomInt(270, 360);
                    else
                        fishInfo[5] = getRandomInt(0, 90);
                    isValid = this.isValidPosition(fishInfo[3], fishInfo[4], fishInfo[5], fishType);
                }
            }
            var fishSize = 1;
            gFishMaxSize.forEach(temp => {
                if(temp.fishTypeId == fishInfo[2] && temp.maxSize > 5)
                {
                    var sizeRandom = getRandomInt(0, 100);
                    if(sizeRandom > 0 && sizeRandom <= 20)
                        fishSize = temp.maxSize;
                    else if(sizeRandom > 20 && sizeRandom <= 50)
                        fishSize = getRandomInt(0,temp.maxSize);
                    else 
                        fishSize = 1;
                }
            })
            fishInfo[8] = fishSize;
            this.currentFishIndex += fishSize;
            var tempFish = fishTypeInfo.find(x=>x.fishType == fishType);
            if(tempFish != undefined)
            {
                for(var k = 0; k < fishSize; k++)
                {
                    var odd = tempFish.odds;
                    if(fishInfo[2] == 80 || fishInfo[2] == 25)
                        odd = getRandomInt(150, 250);
                    var newFishInfo = {
                        id: fishInfo[1] + k,
                        fishType: fishInfo[2],
                        odds: odd,
                        maxOdds: tempFish.maxOdds,
                        birthDate: new Date(),
                        feature: fishInfo[6],
                        prizeRatio: tempFish.prizeRatio,
                        fishTypeGroup: fishTypeGroup,
                        isLevelFish: false
                    };
                    if( fishType == 83 || fishType == 84 || fishType == 93)
                        newFishInfo.odds = this.accumulatedOdd;

                    if(fishType == 111 || fishType == 114 || fishType == 115 || fishType == 113) 
                    {
                        //king of crab level fishes
                        newFishInfo.isLevelFish = true;
                        if(fishType == 114)
                            newFishInfo.level = this.bulletCounter.lobsterInfo.cur_level;
                        else if(fishType == 113)
                            newFishInfo.level = this.bulletCounter.edragonInfo.cur_level;
                        else if(fishType == 111)
                            newFishInfo.level = this.bulletCounter.squidInfo.cur_level;
                        else if(fishType == 115)
                            newFishInfo.level = this.bulletCounter.slotCrabInfo.cur_level;
                    }
                    this.fishInfos.push(newFishInfo);                    
                }
                fishReply.data[11].push(fishInfo);
            }
        }
        
        // console.log("generated fish: " + JSON.stringify(fishReply));
        return fishReply;
    }

    addArmyFish(id)
    {
        var fish = this.armyInfo.find(x => x.id == id);
        if(fish != undefined)
        {
            this.fishInfos.push({
                id: fish.id,
                birthDate: fish.birthDate,
                fishType: fish.fishType,
                fishTypeGroup: fish.fishTypeGroup,
                maxOdds: fish.maxOdds,
                odds: fish.odds,
                prizeRatio: fish.prizeRatio,
                bulletHit: 0,
            });
            //  console.log("added fish: " + fish.id + " odds: " + fish.odds + " maxOdds: " + fish.maxOdds);
        }
    }

    fishOut(fishId)
    {
        var id = this.fishInfos.findIndex(x => x.id == fishId);        
        if(id > -1)
        {
            var type = this.fishInfos[id].fishType;
            this.fishInfos.splice(id, 1);
            if(type == 24)
            {
                // console.log("crab out");
                this.crabOutTime = new Date();
            }
                
            //console.log("remaining fishes: " + this.fishInfos.length);
        }
    }

    update()
    {
        this.clients.forEach(fishPlayer => {
            var current = new Date();
            if((current.getTime() - fishPlayer.syncTime.getTime()) / 1000 > this.redisSyncUnit)
            {
                //sync player data if played more than redisSyncUnit
                redisBridge.synchronizePlayerToDB(fishPlayer);
                fishPlayer.syncTime = new Date();
            }

            if (this.gameType == 'gf' && current.getTime() - fishPlayer.pingTime >= 140000)
            {
                fishPlayer.socket.close();
            }
        });
    }

    startRoom()
    {
        this.isAvailable = true;
        this.mainScriptStartTime = new Date();
    }

    isEnoughBulletHit(fishInfo, betwin, player)
    {
        var hit = this.bulletCounter.getHitCount(fishInfo.fishType, player);
        var targetHit = this.bulletCounter.getBulletLimit(fishInfo, player);
        if(!betwin)
            targetHit = targetHit * getRandomInt(3, 6);
        var res = hit >= targetHit;
        // console.log("fish type: " + fishInfo.fishType + ", current bullet hit: " + hit + " target bullet hit: " + targetHit);
        // if(res)
        //     this.bulletCounter.clearHit(fishInfo.fishType, player);
        return res;
    }

    hit(fishInfo, player, count)
    {
        this.bulletCounter.hit(fishInfo.fishType, player, count);

        if(player.is_demo == 0)
        {
        if(catchedFishes[this.fishGameType][fishInfo.fishType] == undefined)
        {
            var newFish = {
                'fishType' : fishInfo.fishType,
                'odd' : 0,            
                'bulletCount' : count,
                'gameType' : this.fishGameType
            }
            catchedFishes[this.fishGameType][fishInfo.fishType] = newFish;
        }
        else
        {
            catchedFishes[this.fishGameType][fishInfo.fishType].bulletCount += count;
        }
        }   
    }

    resetHit(fishInfo, player, win)
    {
        if(player.is_demo == 0)
        {
        var odd = win / player.bet_value;
        player.winFish.push({id: fishInfo.fishType, odd: odd, bet: player.bet_value, bulletCnt: player.room.bulletCounter.getBulletLimit(fishInfo, player)});

        var newFish = {
            'fishType' : fishInfo.fishType,
            'odd' : odd,            
            'bulletCount' : 0,
            'gameType' : this.fishGameType
        }
        if(catchedFishes[this.fishGameType][fishInfo.fishType] == undefined)
        {
            catchedFishes[this.fishGameType][fishInfo.fishType] = newFish;
        }
        else
        {
            catchedFishes[this.fishGameType][fishInfo.fishType].odd += odd;
        }
            
            // var fs = require('fs');
            // fs.writeFile(__dirname + '/catchedFish.json', JSON.stringify(catchedFishes), function(e){
            //     if(e)
            //         console.log(e);
            // });
        }
        this.bulletCounter.resetHit(fishInfo.fishType, player, win);        
    }

    NormalPathBound(t)
    {
        switch (t) {
            case 80:
            case 81:
            case 82:
                return 500;
            case 18:
                return 300;
            default:
                return 250
            }
    }

    degreesToRadians(angle)
    {
        return angle * Math.PI / 180;
    }

    isValidPosition(x,y,angle, fishType)
    {
        this.x = x;
        this.y = y;
        this.o = angle;
        this.type = fishType;
        var width = 1136;
        var height = 640;
        var t = new Vec2(width, height);
        this.x -= 45,
        this.y -= 45,
        this.y *= -1,
        this.o += 90;
        var e = .004 * t.x * this.x;
        var n = .004 * t.y * this.y;
        var i = Math.tan(-this.degreesToRadians(this.o));
        var o = -i * e + n;
        var s = this.NormalPathBound(this.type);
        var r = .5 * -t.x - s;
        var a = .5 * t.x + s;
        var c = .5 * t.y + s;
        var l = .5 * -t.y - s;
        var u = [Vec2(r, i * r + o), Vec2((o - l) / -i, l), Vec2(a, i * a + o), Vec2((o - c) / -i, c)];
        var h = [0, 1];
        var p = [Vec2(Vec2(0,0).subtract(u[0])).length(), Vec2(Vec2(0,0).subtract(u[1])).length()];
    
        for (var d = 1; d < u.length; d++) {
            var y = Vec2(Vec2(0,0).subtract(u[d])).length();
            y <= p[0] ? (p[1] = p[0], p[0] = y, h[1] = h[0], h[0] = d) : y <= p[1] && (p[1] = y, h[1] = d)
        }
        var y = u[h[0]].y;
        var g = u[h[1]].y;
        this.o > 180 && this.o < 360 ?
            y < g ? (this.startPos = u[h[0]],this.targetPos = u[h[1]]) : (this.startPos = u[h[1]],this.targetPos = u[h[0]])
        : 
            y < g ? (this.startPos = u[h[1]],this.targetPos = u[h[0]]) : (this.startPos = u[h[0]],this.targetPos = u[h[1]]);

        // console.log("start pos: (" + this.startPos.x + ", " + this.startPos.y + ") target pos: (" + this.targetPos.x + ", " + this.targetPos.y + ")");
        var centerPos = Vec2((this.startPos.x + this.targetPos.x) / 2, (this.startPos.y + this.targetPos.y) / 2);
        // if(centerPos.x >= 0 && centerPos.y >= 0)
        // {
        //     if(gAllFishInfo[fishType]!= undefined)
        //         console.log("fish created, type: " + gAllFishInfo[fishType].path_type + " center pos: (" + centerPos.x + ", " + centerPos.y + ")");
        // }
        return centerPos.x >= 0 && centerPos.y >=0;
    }

    getAvailableId()
    {
        var routeId = getRandomInt(1,109);
        var singleRoute = global.gRoutes.find(x => x.id == routeId);
        while(singleRoute.line.length <= 2 || (singleRoute.line[0].x > 0 && singleRoute.line[0].y > 0))
        {
            routeId = getRandomInt(1,109);
            singleRoute = global.gRoutes.find(x => x.id == routeId);
        }
        return routeId;
    }
}

module.exports = {GameRoom}