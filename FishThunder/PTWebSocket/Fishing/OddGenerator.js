/**
 * OddGenerator.js — Boss & Skill Fish Payout Odds (Fishing Game Server)
 *
 * Manages the payout multipliers ("odds") for every boss fish and skill fish
 * across all 12 fishing game types. Normal (low-value) fish use the BulletCounter
 * system instead — this class handles only the special high-value targets.
 *
 * HOW THE ODDS ROTATION WORKS:
 * Each fish entry has:
 *   - `odds`     : an array of possible multiplier values
 *   - `indicator`: the current index into the odds array (advances on each hit)
 *
 * When a player catches a boss fish, getOdd() reads odds[indicator] and applies a
 * ±20% random variance.  setNextOddIndicator() then advances the pointer so the
 * next player who catches that same fish type gets the next value in the list.
 *
 * This creates a pseudo-random but statistically controlled payout sequence —
 * all values in the odds array will be visited in order, ensuring the house edge
 * stays within design parameters over a large number of catches.
 *
 * LEVEL FISH (KC game — King of Crab):
 * Some fish (squid=111, edragon=113, lobster=114, slot_crab=115) have multiple
 * "levels" (phases), each with their own independent odds array and indicator.
 * The `indicator` and `odds` fields are arrays of arrays for these fish.
 *
 * ODDS SCALE:
 * Values like 500, 1000, 1500 are raw multipliers applied against the player's
 * current bet_value.  Final payout = odds × bet_value × coinrate / 10.
 */

const { count } = require("console");
const { getRandomInt, getRandomIntInclusive } = require("./utils");

class OddGenerator
{
    constructor()
    {
        this.fishOdds = {};

        this.fishOdds['MF'] = {
            //boss fishes            
            '25' : {
                indicator: 0,
                odds: [500, 250, 500, 500, 500, 500, 250, 300, 300, 500, 200, 200]  //mermaid
            },            
            '35' : {
                indicator: 0,
                odds: [450, 500, 630, 500, 600, 500, 600, 500, 500, 500, 550, 450] //darkness monster
            },
            '61' : {
                indicator: 0,
                odds: [500, 500, 450, 500, 300, 500, 200, 300, 300, 500, 450, 480] //octopus
            },
            '62' : {
                indicator: 0,
                odds: [500, 500, 450, 500, 300, 500, 200, 300, 300, 500, 450, 480] //octopus
            },
            '63' : {
                indicator: 0,
                odds: [500, 500, 450, 500, 300, 500, 200, 300, 300, 500, 450, 480] //octopus
            },
            '64' : {
                indicator: 0,
                odds: [500, 500, 450, 500, 300, 500, 200, 300, 300, 500, 450, 480] //octopus
            },
            '65' : {
                indicator: 0,
                odds: [500, 500, 450, 500, 300, 500, 200, 300, 300, 500, 450, 480] //octopus
            },
            '66' : {
                indicator: 0,
                odds: [500, 500, 450, 500, 300, 500, 200, 300, 300, 500, 450, 480] //octopus
            },
            '78' : {
                indicator: 0,
                odds: [800, 1000, 800, 800, 850, 900, 800, 1000, 1000, 800, 1000, 950] //thunder dragon
            },
            '80' : {
                indicator: 0,
                odds: [150, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //phoenix
            },            
            '116' : {
                indicator: 0,
                odds: [500, 700, 800, 1200, 1000, 1500, 400, 800, 1800, 400, 1000] //god wealth
            },  
            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [500, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['ALD'] = {
            //boss fishes            
            '25' : {
                indicator: 0,
                odds: [500, 400, 480, 500, 500, 490, 200, 500, 290, 400, 370, 450] //mermaid
            },            
            '78' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //thunder dragon
            },
            '80' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //phoenix
            },            
            '82' : {
                indicator: 0,
                odds: [1000, 1000, 350, 500, 600, 500, 300, 1000, 1000, 800, 400, 200] //genie
            },
            '83' : {
                indicator: 0,
                odds: [800, 1000, 650, 800, 600, 890, 500, 1000, 1000, 800, 200, 800] //aladdin
            },

            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['KK'] = {
            //boss fishes            
            '25' : {
                indicator: 0,
                odds: [500, 400, 450, 500, 400, 350, 500, 500, 200, 450, 500, 200] //mermaid
            },            
            '78' : {
                indicator: 0,
                odds: [800, 1000, 800, 800, 850, 900, 800, 1000, 1000, 800, 1000, 950] //thunder dragon
            },
            '80' : {
                indicator: 0,
                odds: [1000, 900, 700, 780, 600, 960, 750, 1000, 1000, 800, 600, 850] //phoenix
            },
            '81' : {
                indicator: 0,
                odds: [800, 1000, 700, 900, 600, 780, 850, 1000, 1000, 800, 400, 800] //kingkong
            },
            '82' : {
                indicator: 0,
                odds: [700, 1000, 800, 860, 600, 900, 600, 1000, 1000, 800, 550, 400] //frenzied fury
            },            

            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 250, 230, 240, 250, 210, 220, 200] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['LS'] = {
            //boss fishes            
            '24' : {
                indicator: 0,
                odds: [250, 500, 200, 500, 500, 500, 200, 500, 500, 300, 200, 200] //emperor crab
            },
            '25' : {
                indicator: 0,
                odds: [250, 500, 500, 500, 600, 500, 200, 200, 200, 300, 200, 200] //mermaid
            },            
            '80' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //phoenix
            },
            '81' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //buffalo
            },
            '82' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //lucky shamrock
            },
            
            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['MTY'] = {
            //boss fishes           
            '78' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //thunder dragon
            },
            '80' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //phoenix
            },            
            '84' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //lucky buddha
            },
            '85' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //dragon turtle
            },
            '86' : {
                indicator: 0,
                odds: [1000, 900, 700, 500, 600, 960, 750, 600, 1000, 800, 600, 850] //dancing lion
            },
            
            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['TD'] = {
            //boss fishes
            '19' : {
                indicator: 0,
                odds: [1000, 400, 800, 500, 600, 500, 500, 400, 800, 200] //fire dragon
            },
            '20' : {
                indicator: 0,
                odds: [800, 1000, 150, 500, 600, 500, 200, 600, 1000, 800, 700, 500] //crocodile
            },
            '24' : {
                indicator: 0,
                odds: [800, 1000, 700, 900, 600, 500, 800, 1000, 1000, 800, 600, 500] //emperor crab
            },
            '25' : {
                indicator: 0,
                odds: [250, 350, 150, 400, 600, 500, 200, 400, 150, 300, 200, 200] //mermaid
            },
            '29' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] //treasure crab
            },
            '32' : {
                indicator: 0,
                odds: [250, 500, 350, 500, 600, 500, 200, 700, 200] //dragon turtle
            },
            '35' : {
                indicator: 0,
                odds: [250, 700, 150, 500, 600, 500, 200, 700, 1000, 800, 200, 200] //darkness monster
            },
            '61' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 500, 1000, 800, 200, 200] //octopus
            },
            '62' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 600, 1000, 800, 200, 200] //octopus
            },
            '63' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 400, 1000, 800, 200, 200] //octopus
            },
            '64' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 300, 1000, 800, 200, 200] //octopus
            },
            '65' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 800, 1000, 800, 200, 200] //octopus
            },
            '66' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 500, 1000, 800, 200, 200] //octopus
            },
            '78' : {
                indicator: 0,
                odds: [800, 1000, 650, 500, 600, 500, 700, 1000, 1000, 800, 500, 900] //thunder dragon
            },
            '80' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] //phoenix
            },            
            '87' : {
                indicator: 0,
                odds: [800, 500, 450, 500, 600, 500, 200, 200, 200] //freeze dragon
            },
            '88' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] //flare dragon
            },           

            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['WC'] = {
            //boss fishes           
            '24' : {
                indicator: 0,
                odds: [500, 450, 500, 500, 600, 500, 200, 300, 450, 500, 200, 480] //emperor crab
            },
            '25' : {
                indicator: 0,
                odds: [450, 500, 350, 500, 600, 500, 430, 500, 420, 500, 350, 490] //mermaid
            },            
            '78' : {
                indicator: 0,
                odds: [800, 1000, 800, 800, 850, 900, 800, 1000, 1000, 800, 1000, 950] //thunder dragon
            },
            '80' : {
                indicator: 0,
                odds: [800, 1000, 800, 800, 850, 900, 800, 1000, 1000, 800, 1000, 950] //phoenix
            },            
            '92' : {
                indicator: 0,
                odds: [1000, 800, 700, 950, 600, 500, 680, 1000, 1000, 800, 750, 850] //wonder cat
            },

            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['ZB'] = {
            //boss fishes            
            '20' : {
                indicator: 0,
                odds: [250, 500, 150, 500, 500, 500, 200, 200, 300, 500, 200, 200] //crocodile
            },            
            '25' : {
                indicator: 0,
                odds: [250, 500, 250, 500, 500, 500, 200, 500, 500, 300, 200, 200] //mermaid
            },            
            '78' : {
                indicator: 0,
                odds: [800, 1000, 800, 800, 850, 900, 800, 1000, 1000, 800, 1000, 950] //thunder dragon
            },
            '80' : {
                indicator: 0,
                odds: [800, 500, 800, 900, 600, 800, 200, 1000, 1000, 800, 200, 800] //phoenix
            },            
            '82' : {
                indicator: 0,
                odds: [700, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 900] //skull
            },
            '83' : {
                indicator: 0,
                odds: [600, 1000, 800, 500, 700, 900, 200, 1000, 1000, 800, 200, 800] //coffin
            },            

            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['CS'] = {
            //boss fishes           
            '78' : {
                indicator: 0,
                odds: [800, 1000, 800, 800, 850, 900, 800, 1000, 1000, 800, 1000, 950] //thunder dragon
            },
            '80' : {
                indicator: 0,
                odds: [800, 200, 800, 800, 250, 900, 800, 1000, 300, 800, 1000, 250] //phoenix
            },            
            '93' : {
                indicator: 0,
                odds: [800, 500, 800, 900, 600, 800, 150, 1000, 1000, 800, 150, 800] //circus clown
            },
            '94' : {
                indicator: 0,
                odds: [800, 500, 800, 900, 600, 800, 150, 1000, 1000, 800, 150, 800] //circus pig
            },
            '95' : {
                indicator: 0,
                odds: [800, 300, 800, 900, 300, 800, 150, 1000, 1000, 800, 200, 800] //circus tiger
            },
            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };

        this.fishOdds['KC'] = {
            //boss fishes           
            '111' : {
                indicator: [0],
                odds: [[800, 150, 200, 700, 600, 500, 800, 1000, 1000, 800, 200, 200, 700, 600]] //eternal squid
            },
            '112' : {
                indicator: 0,
                odds: [800, 200, 240, 700, 600, 150, 700, 1000, 1000, 230, 400, 200] //eternal phoenix
            },
            '113' : { 
                indicator: [0, 0],
                odds: [[70, 100, 80, 130, 50, 70, 120, 170, 150, 100, 60, 100, 50],                        
                       [500, 850, 600, 700, 600, 650, 790, 1000, 1000, 800, 750, 1000]]
            },
            '114' : { //lobster
                indicator: [0, 0, 0],
                odds: [[70, 100, 80, 130, 50, 70, 120, 170, 150, 100, 60, 100, 50], 
                       [200, 110, 150, 130, 170, 150, 250, 120, 150, 180, 200, 250, 280, 100],
                       [500, 300, 600, 700, 600, 650, 200, 1000, 1000, 800, 200, 200]]
            },
            '115' : { //slot crab
                indicator: [0, 0, 0],
                odds: [[30, 50, 30, 80, 50, 50, 80, 30, 50, 30, 80, 30, 30, 50, 50, 50, 80, 80], 
                       [120, 200, 120, 200, 300, 200, 120, 120, 200, 200, 300, 300, 120, 200],
                       [500, 500, 1000, 1000, 2000, 500, 500, 1000, 1000]]
            },
            //skill fishes
            '22' : {
                indicator: 0,
                odds: [450, 170, 200, 240, 180, 210, 220, 160] //laser crab
            },
            '23' : {
                indicator: 0,
                odds: [220, 450, 200, 240, 190, 500, 220, 160] //drill crab
            },
            '34' : {
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160] //bomb crab
            },
            '77' : {
                indicator: 0,
                odds: [150, 150, 200, 250, 190, 240, 220, 160] //wheel crab
            },
            '79' : {
                indicator: 0,
                odds: [460, 150, 200, 240, 150, 210, 210, 160] //hammer crab
            },
        };
        this.fishOdds['BF'] = {
            //boss fishes 
            '34' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] 
            }, 
            '35' : {
                indicator: 0,
                odds: [800, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] //phoenix
            }, 
            '23' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] 
            }, 
            '25' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] 
            }, 
            '26' : {
                indicator: 0,
                odds: [600, 1000, 550, 550, 600, 800, 900, 1000, 1000, 800, 700, 600]  //thunder dragon
            }, 
            '40' : {
                indicator: 0,
                odds: [1000, 800, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] //buffalo
            },            
            //skill fishes           
            '27':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
            '28':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
            '29':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
            '30':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
            '31':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
        };

        this.fishOdds['CA'] = {
            //boss fishes 
            '20' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200]
            }, 
            '21' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200]
            }, 
            '22' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] 
            },
            '23' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200]
            }, 
            '24' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] 
            }, 
            '25' : {
                indicator: 0,
                odds: [250, 1000, 150, 500, 600, 500, 200, 1000, 1000, 800, 200, 200] 
            }, 
            '26' : {
                indicator: 0,
                odds: [800, 900, 550, 1000, 600, 800, 900, 1000, 1000, 800, 700, 600] 
            },            
            //skill fishes           
            '27':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
            '28':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
            '29':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
            '30':{
                indicator: 0,
                odds: [220, 150, 200, 240, 190, 210, 220, 160]
            },
        };
    }

    getOdd(fish, player)
    {   
        var fishId = fish.fishType;
        var info = this.fishOdds[player.room.fishGameType][fishId];
        var indicator = info.indicator;
        var odds = info.odds;
        if(fish.isLevelFish)
        {
            indicator = info.indicator[fish.level];
            odds = info.odds[fish.level];
        }

        if(fish.fishType == 115) //for slot crab
            return odds[indicator];
        var odd_min = Math.floor(odds[indicator] * getRandomIntInclusive(8, 10) / 10);  
        var odd_max = Math.floor(odds[indicator] * getRandomIntInclusive(10, 12) / 10);
        var odd = getRandomIntInclusive(odd_min, odd_max);
        return odd;
    }

    setNextOddIndicator(fish, player)
    {
        var fishId = fish.fishType;
        var info = this.fishOdds[player.room.fishGameType][fishId];
        if(fish.isLevelFish)
            info.indicator[fish.level] = (info.indicator[fish.level] + 1) % info.odds[fish.level].length;
        else
            info.indicator = (info.indicator + 1) % info.odds.length;
    }
}

module.exports = {OddGenerator}