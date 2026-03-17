/**
 * Common.js — Shared Game Constants (Fishing Game Server)
 *
 * Central registry for all game type identifiers, their full names as stored in the
 * Laravel database, and the fish IDs that are considered "boss" fish (special high-value
 * targets that require elevated odds logic vs. normal fish).
 *
 * Short codes (e.g. 'MF') are used throughout the Node.js game server for fast lookups.
 * Full names (e.g. 'MonsterFrenzyPGD') must match exactly what is stored in w_games.name
 * in the MySQL database, since they are used in SQL queries for RTP/settings lookups.
 */
class Common
{
    /**
     * All active fishing game type codes.
     * Each code maps to one game room type with its own fish set, boss fish, and odds table.
     *   MF  = Monster Frenzy
     *   ALD = Aladdin Adventure
     *   KK  = King Kong
     *   LS  = Lucky Shamrock
     *   MTY = Fortune Kings (Motu Yuen)
     *   TD  = Thunder Dragon
     *   WC  = Wonder Cat
     *   ZB  = Ghost (Zombie)
     *   CS  = Circus
     *   KC  = King of Crab
     *   BF  = Buffalo Thunder
     *   CA  = Crab Avengers
     */
    static gameList = ['MF', 'ALD', 'KK', 'LS', 'MTY', 'TD', 'WC', 'ZB', 'CS', 'KC', 'BF', 'CA'];

    /**
     * Maps short game codes → full game names stored in the Laravel DB (w_games.name).
     * Used by RTPWaver and RedisBridge when querying per-game RTP/settings.
     */
    static gameNames = {
        'MF' : 'MonsterFrenzyPGD',
        'ALD' : 'AladdinAdventurePGD',
        'KK' : 'FishHunterKingKongPGD',
        'LS' : 'FishHunterLuckyShamrockPGD',
        'MTY' : 'FishFortuneKingsPGD',
        'TD' : 'FishHunterThunderDragonPGD',
        'WC' : 'FishWonderCatPGD',
        'ZB' : 'FishHunterGhostPGD',
        'CS' : 'FishCircusPGD',
        'KC' : 'FishKingOfCrabPGD',
        'BF' : 'BuffaloThunder',
        'CA' : 'CrabAvengers',
    }

    /**
     * Fish IDs that are "boss" fish in the standard 'gd' game type.
     * Boss fish use the OddGenerator's rotating odds table (not the BulletCounter's
     * random bullet-count system) and trigger special UI effects on the client.
     * IDs 22,23,34,77,79 are skill-crabs (activate player special abilities when caught).
     */
    static bossFishIds = [19, 20, 24, 25, 29, 32, 35, 61, 62, 63, 64, 65, 66, 78, 80, 81, 82, 83, 84, 85, 86, 87, 88, 92, 116, 22, 23, 34, 77, 79];

    /**
     * Boss fish IDs for the 'gf' game type (Buffalo Thunder / Crab Avengers variant).
     * These games have a 6-seat layout and a different set of boss fish.
     */
    static bossFishIdsGF = [27,28,29,30,31,34,35,23,25,26,40,20,21,22,23,24];
}

module.exports = {Common}