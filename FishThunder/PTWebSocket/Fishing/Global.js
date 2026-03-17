/**
 * Global.js — Process-Wide Shared State (Fishing Game Server)
 *
 * Declares all global variables that are shared across every module in the fishing
 * game server. These are set once at startup (during game config loading) and then
 * read by GameRoom and Player instances throughout the server's lifetime.
 *
 * Why globals here instead of module imports?
 * These arrays are populated asynchronously at boot time from JSON config files and
 * the database. Using Node's `global` object makes them accessible in any module
 * without circular dependency issues.
 *
 * Variable overview:
 *   gFishMaxSize       - Max hitbox/size per fish type, used for collision detection
 *   gAllFishInfo       - Master fish data (type, speed, odds range, etc.) loaded from config JSON
 *   groupPosIndex      - Tracks current spawn position within a fish group/school pattern
 *   normalFishFormat   - Spawn script templates for normal (non-skill) fish waves
 *   skillFishFormat    - Spawn script templates for skill-trigger fish (crabs that activate abilities)
 *   vortexFishFormat   - Spawn script templates for vortex/whirlpool special events
 *   lightingChainFormat- Spawn script templates for chain-lightning special events
 *   samplePoints       - Pre-calculated path waypoints for fish movement routes
 *   gTides             - Tide event configurations (tides = timed waves of stronger/more fish)
 *   redisBridge        - Singleton RedisBridge instance, shared by all Player objects
 *   gRoutes            - Pre-defined swimming path routes fish follow across the screen
 */

global.gFishMaxSize = [];
global.gAllFishInfo = [];
global.groupPosIndex = [];
global.normalFishFormat = [];
global.skillFishFormat = [];
global.vortexFishFormat = [];
global.lightingChainFormat = [];
global.samplePoints = [];
global.gTides = [];
global.redisBridge = null;  // Set to a RedisBridge instance after server starts
global.gRoutes = [];

module.exports = {gFishMaxSize, gAllFishInfo, normalFishFormat, skillFishFormat, vortexFishFormat, lightingChainFormat, samplePoints, gTides, gRoutes, groupPosIndex};