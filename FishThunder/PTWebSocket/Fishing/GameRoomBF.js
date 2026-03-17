const { BulletCounter } = require('./BulletCounter.js');
var GameRoom = require('./GameRoom.js').GameRoom;
var getRandomInt = require('./utils.js').getRandomInt;
var global = require('./Global.js');
var fs = require('fs');
class GameRoomBF extends GameRoom
{
    constructor()
    {
        super('gf');
        this.tideStatus = 'startTide';
        this.fishGameType = 'BF';
        this.bulletCounter = bulletCounterManager.getBulletCounter(this.fishGameType);
        this.isAvailable = false;
        this.WealthGodOdd = 0;
        this.WealthGodLastOdd = 0;
        this.roomType = 0;
        this.maxRoomNormalOdds = 400;
        this.WealthGodOdd = getRandomInt(300,350);
        var ratio = getRandomInt(0,100);
        if (0 <= ratio && ratio< 25)
        {
            this.WealthGodLastOdd = getRandomInt(400,600);
        } else if (25 <= ratio && ratio< 50)
        {
            this.WealthGodLastOdd = getRandomInt(600,1000);
        } else if (50 <= ratio && ratio< 75)
        {
            this.WealthGodLastOdd = getRandomInt(1000,1500);
        } else if (75 <= ratio && ratio <= 100)
        {
            this.WealthGodLastOdd = getRandomInt(1500,2000);
        } else 
        {
            this.WealthGodLastOdd = getRandomInt(400,600);
        }
        
        //this.scriptList = [1,2,3,4,5,6,3,4];
        this.scriptList = [1,2,3,4,5,6,7,8];
        this.scriptPeriods = {
            1: 315,
            2: 125,
            3: 315,
            4: 125,
            5: 315,
            6: 125,
            7: 315,
            8: 125,
        };
        this.scriptIndex = 0;
        this.randomFish = getRandomInt(2,5);
        this.isDebug = false;
        if (this.isDebug)
        {
            this.scriptIndex = 3;
            this.tideStatus = 'endTide';
            this.randomFish = 20;
            this.scriptPeriods = {
                1: 200,  
                2: 100,  
                3: 200,  //sand background
                4: 100,  
                5: 200,  
                6: 100,  
                7: 200,  
                8: 100,  
            };  
        }
        
        this.bossFishLimited = [24,22,25,23];

        this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
        this.scriptPrepareTime = 5;
        this.lastBoss = 26;
        this.intervalID = setInterval(() => {
            this.run();
        }, 1000);        
        this.fishGroupScenario = [];
        //Buffalo Thunder Init
        this.fishInfoBF = [
            {
                fishType: 1,
                odds:2,
                maxOdds: 2,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 2,
                odds:3,
                maxOdds: 3,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 3,
                odds:4,
                maxOdds: 4,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 4,
                odds:5,
                maxOdds: 5,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 5,
                odds:6,
                maxOdds: 6,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 6,
                odds:7,
                maxOdds: 7,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 7,
                odds:8,
                maxOdds: 8,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 8,
                odds:9,
                maxOdds: 9,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 9,
                odds:10,
                maxOdds: 10,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 10,
                odds:12,
                maxOdds: 12,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 11,
                odds:15,
                maxOdds: 15,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 12,
                odds:18,
                maxOdds: 18,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 13,
                odds:20,
                maxOdds: 20,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 14,
                odds:20,
                maxOdds: 60,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 15,
                odds:20,
                maxOdds: 60,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 16,
                odds:20,
                maxOdds: 60,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 17,
                odds:30,
                maxOdds: 100,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 18,
                odds:60,
                maxOdds: 200,
                fishTypeGroup:'normal'
            },  
            {
                fishType: 19,
                odds:350,
                maxOdds: 350,
                fishTypeGroup:'normal'
            },
            {
                fishType: 27,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'skill'
            },
            {
                fishType: 28,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'skill'
            },
            {
                fishType: 29,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'skill'
            },
            {
                fishType: 30,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'skill'
            },
            {
                fishType: 31,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'skill'
            },
            {
                fishType: 34,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'boss'
            },
            {
                fishType: 35,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'boss'
            },
            {
                fishType: 23,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'boss'
            },
            {
                fishType: 25,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'boss'
            },
            {
                fishType: 26,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'boss'
            },
            {
                fishType: 40,
                odds: 100,
                maxOdds:1000,
                fishTypeGroup:'boss'
            },
        ];
    }

    fishArrayGenerateBF(fishid,routeId,fishTypeGroup)
    {
        if (fishid == 20)
        {
            routeId = 101;
        } else if (fishid == 21){
            routeId = 110;
        }  else if (fishid == 35)
        {
            routeId = getRandomInt(0,100) < 50 ? 327: 328;
        } else if (fishid == 34)
        {
            routeId = 326;
        } else if (fishid == 40)
        {
            var bfRoots = [321,322,323,324,325,327,328,329,330];
            routeId = bfRoots[getRandomInt(0,bfRoots.length - 1)];
        }else if (fishid == 26)
        {
            routeId = 142;
        }
        var fishId = this.generateFishId();
        var curTime = new Date().getTime();
        var route = global.gRoutes.find(x => x.id == routeId);
        var fishOdd = this.fishInfoBF.find(x => x.fishType == fishid);
        if (fishOdd == null)
        {
            fishOdd = {
                odds:1000, 
                maxOdds: 1000
            };
        }
        
        var odds = getRandomInt(fishOdd.odds,fishOdd.maxOdds);
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
            rate: odds ,
            ext: 0,
            fishTypeGroup: fishTypeGroup,
            odds: odds,
            maxOdds: odds,
        };
        this.fishInfos.push(fishInfo);
        return {id:fishId,classid: fishid ,fishid: fishid,born_time: curTime,routeid: routeId,dead_time: fishInfo.deadTime,offsettype: fishInfo.offsetType,offsetx: fishInfo.offsetX,
            offsety: fishInfo.offsetY,offsetr: fishInfo.offsetR,rate: fishInfo.maxOdds,ext: fishInfo.ext
        };
    }

    generateNormalFishes()
    {
        var fishGroupResponse = {
            message:{
                sprites:[]
            },
            succ: true,
            errinfo: 'ok',
            type: 'increasesprites'
        };

        var smallFishFormat = [1,2,3,4,5,6,7,8,9,10,11,12,13];
        var mediumFishFormat = [14,15,16,17];
        if(this.getTotalSelectedNormalOdds(16) < this.maxRoomNormalOdds)
        {
            var routeId = this.getAvailableId();

            if (!this.checkSpecialTypeFish(18) && getRandomInt(0,100) < 10)
            {
                routeId = getRandomInt(166,175);
                fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(18,routeId,'normal'));
            } else if (!this.checkSpecialTypeFish(19) && getRandomInt(0,100) < 10)
            {
                routeId = getRandomInt(166,175);
                fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(19,routeId,'normal'));
            }else if (!this.checkSpecialTypeFish(14) && !this.checkSpecialTypeFish(15) && !this.checkSpecialTypeFish(16) && !this.checkSpecialTypeFish(17))
            {
                    routeId = this.getAvailableId();
                    fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(mediumFishFormat[getRandomInt(0,mediumFishFormat.length - 1)],routeId,'normal'));
            }else
            {
                for(var i = 0; i < 5; i++)
                {
                    routeId = this.getAvailableId();
                    fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(smallFishFormat[getRandomInt(0,smallFishFormat.length - 1)],routeId,'normal'));
                }
            }
            this.sendToAllUsers(fishGroupResponse); 
        }
    }    

    generateGroupFish()
    {
        var tideTemplate = JSON.parse(fs.readFileSync(__dirname + '/arcade_data/groupFish.json', 'utf8'));
        var fishTideResponse = {
            message:{
                sprites:[]
            },
            succ: true,
            errinfo: 'ok',
            type: 'increasesprites'
        };

        var startTime = tideTemplate[0].born_time;
        for(var i = 0; i < tideTemplate.length; i++)
        {
            if(startTime < tideTemplate[i].born_time)
            {
                startTime = tideTemplate[i].born_time
            }
        }

        for(var i = 0; i < tideTemplate.length; i++)
        {
            tideTemplate[i].born_time = new Date().getTime();
        }
        var routeId = getRandomInt(316,325);

        for(var i = 0; i < tideTemplate.length; i++)
        {
            var tideEle = tideTemplate[i];
            var fishOdd = this.fishInfoBF.find(x => x.fishType == tideEle.fishid);
            if (fishOdd == null)
            {
                fishOdd = {
                    odds:1000, 
                    maxOdds: 1000
                };
            }
            var odds = getRandomInt(fishOdd.odds,fishOdd.maxOdds);
            fishTideResponse.message.sprites.push(this.fishArryGenerateGroupDetail(1,1,tideEle.born_time,routeId,tideEle.offsettype,tideEle.offsetx,tideEle.offsety,tideEle.offsetr,odds,tideEle.ext,'normal'));
        }
        this.sendToAllUsers(fishTideResponse);
        //
    }

    generateTide()
    {

        //tide 3: 60s,2: 15s,1: 30s,4: 60s,5: 40
        //get tide template
        var tideDelay = [20,20,20,20,20,20];
        var tideIdxs = [1,2,3,4,5,6];
        //
        var tideIndex = tideIdxs[getRandomInt(0,tideIdxs.length - 1)];
        var fishTideResponse = {
            message:{
                sprites:[]
            },
            succ: true,
            errinfo: 'ok',
            type: 'increasesprites'
        };
        var tideTime = tideDelay[tideIndex - 1];
        if (this.isDebug)
        {
            tideIndex = 4;
        }
        let self = this;
        switch(tideIndex)
        {
            case 1:
                break;
            case 2:
                setTimeout(function(){self.fishInfos = []},55*1000);
                break;
            case 3:
                setTimeout(function(){self.fishInfos = []},92*1000);
                break;
            case 4:
                setTimeout(function(){self.fishInfos = []},73*1000);
                break;
            case 5:
                setTimeout(function(){self.fishInfos = []},80*1000);
                break;
            case 6:
                setTimeout(function(){self.fishInfos = []},55*1000);
                break;
        }
        var tideTemplate = JSON.parse(fs.readFileSync(__dirname + '/arcade_data/tide0' + tideIndex + '_gf.json', 'utf8'));
        var bornTime = new Date().getTime() + tideTime * 1000;

        var startTime = tideTemplate[0].born_time;
        for(var i = 0; i < tideTemplate.length; i++)
        {
            if(startTime > tideTemplate[i].born_time)
            {
                startTime = tideTemplate[i].born_time
            }
        }

        for(var i = 0; i < tideTemplate.length; i++)
        {
            var alive = tideTemplate[i].dead_time - tideTemplate[i].born_time;
            tideTemplate[i].born_time = tideTemplate[i].born_time - startTime + bornTime;
            tideTemplate[i].dead_time = alive + tideTemplate[i].born_time;
        }

        for(var i = 0; i < tideTemplate.length; i++)
        {
            var tideEle = tideTemplate[i];
            var fishOdd = this.fishInfoBF.find(x => x.fishType == tideEle.fishid);
            if (fishOdd == null)
            {
                fishOdd = {
                    odds:1000, 
                    maxOdds: 1000
                };
            }
            var odds = getRandomInt(fishOdd.odds,fishOdd.maxOdds);
            fishTideResponse.message.sprites.push(this.fishArryGenerateGroupDetail(tideEle.classid,tideEle.fishid,tideEle.born_time,tideEle.routeid,tideEle.offsettype,tideEle.offsetx,tideEle.offsety,tideEle.offsetr,odds,tideEle.ext,'normal',tideTemplate[i].dead_time));
        }
        this.sendToAllUsers(fishTideResponse);
        //
    }

    run()
    {
        this.update();
        var skillName = '';
        for(var i = 0; i < this.clients.length; i++)
        {
            if (this.clients[i].multiBombSkillSet != null && this.clients[i].multiBombSkillSet.skillName != '')
            {
                skillName = this.clients[i].multiBombSkillSet.skillName;
                break;
            }
        }

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
            if(this.scriptTime <= 0)
            {
                this.scriptIndex = (this.scriptIndex + 1) % this.scriptList.length;
                this.activeSkill = '';
                this.scriptTime = this.scriptPeriods[this.scriptList[this.scriptIndex]];
                if (this.scriptIndex % 2 == 1)
                {
                    this.scriptTime += 3;
                }else {
                    this.scriptTime += 17;
                }
                this.dispatchScriptEvent(this.scriptPeriods[this.scriptList[this.scriptIndex]], this.scriptList[this.scriptIndex]);
                script = this.scriptList[this.scriptIndex];
                if (script == 1 || script == 3 || script == 5)
                {
                    this.tideStatus = 'startTide';
                    this.fishInfos = [];
                    //fish group

                    //
                }
            }
            if(this.scriptPrepareTime > 0)
                this.scriptPrepareTime--;
            if(this.scriptPrepareTime > 0)
                return;

            switch(script)
            {
                case 2:
                    if(!this.checkSpecialTypeFish(25) && skillName == '')
                    {
                        var fishGroupResponse = {
                            message:{
                                sprites:[]
                            },
                            succ: true,
                            errinfo: 'ok',
                            type: 'increasesprites'
                        };
                        var random = getRandomInt(160,165);
                        fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(25,random,'boss'));
                        this.sendToAllUsers(fishGroupResponse);
                    }
                    break;
                case 4:
                case 8:
                    if(!this.checkSpecialTypeFish(40) && skillName == '')
                    {
                        var fishGroupResponse = {
                            message:{
                                sprites:[]
                            },
                            succ: true,
                            errinfo: 'ok',
                            type: 'increasesprites'
                        };
                        fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(40,0,'boss'));
                        this.sendToAllUsers(fishGroupResponse);
                    }
                    break;
                case 6:
                    if(this.checkSpecialTypeFishLen(23) < 2 && skillName == '')
                    {
                        var fishGroupResponse = {
                            message:{
                                sprites:[]
                            },
                            succ: true,
                            errinfo: 'ok',
                            type: 'increasesprites'
                        };
                        var random = getRandomInt(160,175);
                        if (this.checkSpecialTypeFishLen(23) < 1)
                        {
                            random = getRandomInt(170,175);
                        }else{
                            random = getRandomInt(160,165);
                        }
                        fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(23,random,'boss'));
                        this.sendToAllUsers(fishGroupResponse);
                    }
                    break;
                case 1:
                case 3:
                case 5:
                case 7:
                    if (this.tideStatus == 'startTide')
                    {
                        this.generateTide();
                        this.tideStatus = 'tiding';
                        if (script == 1)
                        {
                            this.fishGroupScenario = [26,35,34,40,35,34,40,26,35,40,34,35,40,34,26,40,35,34,35,40,34,35,40,34,34,26,40,35,34,40,35,34];
                        }else{
                            this.fishGroupScenario = [26,40,35,26,40,34,26,34,40,26,35,40,34,26,34,40,26,40,35,26,40,34,34,40,26,35,40,26,34,40,26,34,40,26,35,26,40,34,40,26,34];
                        }
                    }
                    break;
            }

            if ( this.tideStatus == 'tiding' && this.fishInfos.length < 3 )
            {
                this.tideStatus = 'endTide';
            } else if(this.tideStatus == 'endTide')
            {
                if (this.randomFish > 0)
                {
                    this.randomFish--;
                }
                else
                {
                    this.randomFish = getRandomInt(10,15);
                    if (this.isDebug)
                    {
                        //this.randomFish = 20;
                    }
                    if (!this.checkSpecialClassFish(26) && !this.checkSpecialClassFish(35) && !this.checkSpecialClassFish(34) && !this.checkSpecialClassFish(40) && this.scriptIndex % 2 ==0 && skillName == '' && this.scriptTime > 20)
                    {
                        if (this.fishGroupScenario.length > 0 || this.isDebug)
                        {
                            var fishGroupResponse = {
                                message:{
                                    sprites:[]
                                },
                                succ: true,
                                errinfo: 'ok',
                                type: 'increasesprites'
                            };
                            var fishType = this.fishGroupScenario[getRandomInt(0,this.fishGroupScenario.length - 1)];
                            while(fishType == this.lastBoss)
                            {
                                fishType = this.fishGroupScenario[getRandomInt(0,this.fishGroupScenario.length - 1)];
                            }
                            // this.fishGroupScenario.splice(0,1);
                            var random = getRandomInt(1,90);
                            if (this.isDebug)
                            {
                                fishType = 34;
                                this.lastBoss = fishType;
                                fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(fishType,random,'boss'));
                            }else{
                                this.lastBoss = fishType;
                                fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(fishType,random,'boss'));
                            }
                            this.sendToAllUsers(fishGroupResponse);
                        }
                    }
                }

                //normal fish generate
                this.generateNormalFishes();

                if (getRandomInt(0,100) < 3)
                {
                    this.generateGroupFish();
                }
                
                if(!this.checkSpecialTypeFish(27) && !this.checkSpecialTypeFish(28) && !this.checkSpecialTypeFish(29) && !this.checkSpecialTypeFish(30) && !this.checkSpecialTypeFish(31) && getRandomInt(0,10) <= 1 && skillName == '')
                {
                    var fishGroupResponse = {
                        message:{
                            sprites:[]
                        },
                        succ: true,
                        errinfo: 'ok',
                        type: 'increasesprites'
                    };
                    var random = getRandomInt(121,138);

                    var skillFishIds = [27,28,29,30,31];
                    var randomFish = getRandomInt(0,skillFishIds.length);
                    fishGroupResponse.message.sprites.push(this.fishArrayGenerateBF(skillFishIds[randomFish],random,'skill'));
                    this.sendToAllUsers(fishGroupResponse);
                }
                // chain link
                if (!this.checkSpecialClassFish(32) && this.scriptTime > 20)
                {
                    var fishGroupResponse = {
                        message:{
                            sprites:[]
                        },
                        succ: true,
                        errinfo: 'ok',
                        type: 'increasesprites'
                    };
                    var random = this.getAvailableId();
                    var smallFishes = [3,4,5,6,7,8,9,10,11];
                    fishGroupResponse.message.sprites.push(this.fishArryGenerateWithType(smallFishes[getRandomInt(0,smallFishes.length - 1)],random,'chain',32,this.fishInfoBF,300));
                    this.sendToAllUsers(fishGroupResponse);
                }

                // vortex
                if (!this.checkSpecialClassFish(33) && this.scriptTime > 20)
                {
                    var fishGroupResponse = {
                        message:{
                            sprites:[]
                        },
                        succ: true,
                        errinfo: 'ok',
                        type: 'increasesprites'
                    };
                    var random = this.getAvailableId();
                    var smallFishes = [3,4,5,6,7,8,9,10,11];
                    fishGroupResponse.message.sprites.push(this.fishArryGenerateWithType(smallFishes[getRandomInt(0,smallFishes.length - 1)],random,'vortex',33,this.fishInfoBF,200));
                    this.sendToAllUsers(fishGroupResponse);
                }
            }
            
            //remove old fishes
            var tempFishInfos = [...this.fishInfos] ;
            let nowDate = new Date().getTime();
            for(var i = 0; i < tempFishInfos.length; i++)
                {
                if (nowDate > tempFishInfos[i].deadTime)
                {
                    this.fishOut(tempFishInfos[i].id);
                }
                
                }
        }
    }    

    fishOut(fishId)
    {
        super.fishOut(fishId);
    }
}

module.exports = {GameRoomBF}