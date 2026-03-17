const { BulletCounter } = require("./BulletCounter");

class BulletCounterManager
{
    constructor(){
        this.bulletCounters = {};        
    }

    addBulletCounter(gameType)
    {
        var bulletCounter = new BulletCounter(gameType);
        var name = gameType;
        this.bulletCounters[name] = bulletCounter;
    }

    getBulletCounter(gameType)
    {
        var name = gameType;
        return this.bulletCounters[name];
    }
}

module.exports = {BulletCounterManager}