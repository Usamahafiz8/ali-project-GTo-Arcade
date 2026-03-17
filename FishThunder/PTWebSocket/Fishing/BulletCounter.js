const { Common } = require("./Common");
const { getRandomInt, getRandomIntInclusive, in_array } = require("./utils");
var fs = require('fs');
class BulletCounter{
    constructor(gameType){
        this.fishBullets = {};
        this.fishBulletsDemo = {};

        this.fishHits = {};
        this.fishHitsDemo = {};

        this.fishFundInfo = {};
        this.rtpLimitHigh = 97;
        this.rtpLimitLow = 85;
        /**
         * level for betting amount
         * 0: 5 ~ 25
         * 1: 30 ~ 50
         * 2: 55 ~ 100
         * 3: 150 ~ 250
         * 4: 250 ~ 500
         * 5: 500 ~ 700
         * 6: 700 ~ 1000
         */
        this.levels = [0,1,2,3,4,5,6]; 

        this.generateRandomBulletCounts(gameType);

        this.resetLevelFish(111); //king of crab squid
        this.resetLevelFish(113); //king of crab edragon
        this.resetLevelFish(114); //king of crab lobster
        this.resetLevelFish(115); //king of crab slot crab
    }

    generateRandomBulletCounts(gameType)
    {        
        this.fishData = JSON.parse(fs.readFileSync(__dirname + '/game_config/' + gameType + '.json', 'utf8'));
        this.levels.forEach(level => {
            this.fishBullets[level] = {};
            this.fishBulletsDemo[level] = {};
            this.fishFundInfo[level] = {};

            Object.keys(this.fishData).map((id) => {
                var fishInfo = this.fishData[id];
                this.fishBullets[level][id] = this.getRandomBulletCount(fishInfo);
                this.fishBulletsDemo[level][id] = this.getRandomBulletCount(fishInfo);
                this.fishFundInfo[level][id] = {bet: 0, win: 0, direction: 0};    
                // console.log(fishInfo.fish_type + " bullet: " + this.fishBullets[id]);
            });
        });        
    }

    getRandomBulletCount(fishInfo)
    {
        var rate = getRandomIntInclusive(0, 100);        
        var range = fishInfo.range;
        if(fishInfo.isLevelFish)
            range = range[fishInfo.level];
        if(range == undefined)
        {
            // console.log("undefined range for " + fishInfo.fish_type + " setting bullet count as 500");
            return 500;
        }
        else
        {
            var p = 0;
            var hit = 0;
            for(var i = 0; i < range.length; i++)
            {
                var rate_min = p;
                var rate_max = rate_min + range[i].percent;
                if(rate >= rate_min && rate <= rate_max)
                {
                    hit = getRandomIntInclusive(range[i].min, range[i].max);
                    break;
                }
                p += range[i].percent;
            }

            if(hit == 0)
            {
                var obj = range[range.length - 1];
                hit = getRandomIntInclusive(obj.min, obj.max);
            }
            return hit;
        }
    }

    getBetLevel(player, fishType)
    {
        var level = 0;
        if(player.bet_amount > 25 && player.bet_amount <= 50 )
            level = 1;
        else if(player.bet_amount > 50 && player.bet_amount <= 100)
            level = 2;
        else if(player.bet_amount > 100 && player.bet_amount <= 250)
            level = 3;
        else if(player.bet_amount > 250 && player.bet_amount <= 500)
            level = 4;
        else if(player.bet_amount > 500 && player.bet_amount <= 700)
            level = 5;
        else if(player.bet_amount > 700 && player.bet_amount <= 1000)
            level = 6;

        // if(fishType <= 10)
        //     level = 0;
        return level;
    }

    getBulletLimit(fishInfo, player)
    {
        var bet_level = this.getBetLevel(player, fishInfo.fishType);
        if(player.is_demo)
        {
            if(this.fishBulletsDemo[bet_level][fishInfo.fishType] != undefined)
                return this.fishBulletsDemo[bet_level][fishInfo.fishType];
            else
            {
                console.log("no bullet limit definition for fishtype: " + fishInfo.fishType);
                return 500;
            }
        }
        else
        {
            if(this.fishBullets[bet_level][fishInfo.fishType] != undefined)
            {
                return this.fishBullets[bet_level][fishInfo.fishType];
            }
            else
            {
                console.log("no bullet limit definition for fishtype: " + fishInfo.fishType);
                return 500;
            }
        }
    }

    getHitCount(fishType, player)
    {
        var bet_level = this.getBetLevel(player, fishType);
        if(player.is_demo)
        {
            if(this.fishHitsDemo[bet_level][fishType] != undefined)
                return this.fishHitsDemo[bet_level][fishType];
            else
                return 0;
        }
        else
        {
            if(this.fishHits[bet_level][fishType] != undefined)
                return this.fishHits[bet_level][fishType];
            else
                return 0;
        }
    }

    hit(fishType, player, count)
    {
        var bet_level = this.getBetLevel(player, fishType);
        if(player.is_demo)
        {
            if(this.fishHitsDemo[bet_level] == undefined)
                this.fishHitsDemo[bet_level] = {};
            if(this.fishHitsDemo[bet_level][fishType] != undefined)
                this.fishHitsDemo[bet_level][fishType] += count;
            else
            {                
                this.fishHitsDemo[bet_level][fishType] = count;
            }                
        }
        else
        {
            if(this.fishHits[bet_level] == undefined)
                this.fishHits[bet_level] = {};
            if(this.fishHits[bet_level][fishType] != undefined)
                this.fishHits[bet_level][fishType] += count;
            else
            {
                this.fishHits[bet_level][fishType] = count;
            }
                

            if(this.fishFundInfo[bet_level][fishType] != undefined)
                this.fishFundInfo[bet_level][fishType].bet += player.bet_amount / player.hitFishCnt;
            else
            {
                this.fishFundInfo[bet_level][fishType] = {bet: player.bet_amount, win: 0, direction: 0};
            }
        }    
                
        if(fishType == 111)
        {
            //process squid info
            this.squidInfo.bullets += count;
        }
        else if(fishType == 113)
        {
            this.edragonInfo.bullets += count;
        }
        else if(fishType == 114)
        {
            //process lobster            
            this.lobsterInfo.bullets += count;            
        }
        else if(fishType == 115)
        {
            //process lobster            
            this.slotCrabInfo.bullets += count;            
        }        
    }

    clearHit(fishType, player)
    {
        if(player.is_demo)
        {
            this.fishHitsDemo[fishType] = 0;
        }
        else
        {
            this.fishHits[fishType] = 0;
        }
    }

    resetHit(fishType, player, win)
    {
        var bet_level = this.getBetLevel(player, fishType);
        var fish = this.fishData[fishType];
        if(player.is_demo)
        {
            this.fishHitsDemo[bet_level][fishType] = 0;
            
            if(fish != undefined)
            {                
                this.fishBulletsDemo[bet_level][fishType] = this.getRandomBulletCount(fish);
            }            
            else
            {
                console.log("undefined fishtype: " + fishType + " setting bullet as 500");
                this.fishBulletsDemo[bet_level][fishType] = 500;
            }
        }
        else
        {
            if(this.fishFundInfo[bet_level][fishType] != undefined)
                this.fishFundInfo[bet_level][fishType].win += win;
            else
            {
                this.fishFundInfo[bet_level][fishType] = {bet: player.bet_amount, win: win, direction: 0};
            }

            this.fishHits[bet_level][fishType] = 0;

            var fundInfo = this.fishFundInfo[bet_level][fishType];
            var fishRtp = fundInfo.win * 100 / fundInfo.bet;
            if(fish == undefined)
            {
                console.log("undefined fishtype: " + fishType + " setting bullet as 500");
                this.fishBullets[fishType] = 500;
            }            
            else
            {
                if(fundInfo.direction == 0) 
                {
                    //rising direction
                    if(fishRtp < this.rtpLimitHigh)
                    {
                        this.fishBullets[bet_level][fishType] = this.getRandomBulletCount(fish);
                    }
                    else
                    {
                        fundInfo.direction = 1; //change to recover direction
                        this.fishBullets[bet_level][fishType] = parseInt(this.getRandomBulletCount(fish) * getRandomInt(18, 22) / 10);
                    }
                }
                else 
                {
                    //recovering direction
                    if(fishRtp > this.rtpLimitLow)
                    {
                        this.fishBullets[bet_level][fishType] = parseInt(this.getRandomBulletCount(fish) * getRandomInt(18, 22) / 10);
                    }
                    else
                    {
                        fundInfo.direction = 0;
                        this.fishBullets[bet_level][fishType] = this.getRandomBulletCount(fish);
                    }
                }
            }            
        }
    }

    resetLevelFish(fishType)
    {
        switch(fishType)
        {
            case 111: //King of crab squid
                this.squidInfo = {
                    cur_level: 0,
                    cur_size: 50,
                    level_index: 0,
                    levels: [0],
                    sizes: [50],
                    level_bullets: [2000],
                    bullets: 0
                }
                break;
            case 113:
                this.edragonInfo = { //KingOfCrab flaming dragon
                    cur_level: 0,
                    level_index: 0,
                    levels: [0, 1],
                    level_bullets: [100, 3000],
                    bullets: 0
                }
                break;
            case 114:
                this.lobsterInfo = { //KingOfCrab lobster
                    cur_level: 0,
                    cur_size: 50,
                    level_index: 0,
                    levels: [0, 1, 2],
                    sizes: [50, 75, 200],
                    level_bullets: [2000, 3000, 4000],
                    bullets: 0
                }
            case 115:
                this.slotCrabInfo = { //KingOfCrab slot crab
                    cur_level: 0,
                    level_index: 0,
                    levels: [0, 1, 2],
                    level_bullets: [200, 300, 400],
                    bullets: 0
                }
                break;
        }
    }
}

module.exports = {BulletCounter}