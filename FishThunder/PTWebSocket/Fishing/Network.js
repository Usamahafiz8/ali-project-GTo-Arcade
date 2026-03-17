/**
 * Network.js — WebSocket Message Sender (Fishing Game Server)
 *
 * Provides a single helper function used by every part of the fishing game server
 * whenever it needs to push a message to a connected player's WebSocket.
 *
 * WHY TWO DIFFERENT PROTOCOLS?
 * The platform supports two distinct game client types with different wire formats:
 *
 *   'gd' (standard PGD games — MonsterFrenzy, KingKong, etc.):
 *       Uses a length-prefixed protocol: first 4 bytes = zero-padded message length,
 *       followed by the JSON body.  e.g.  "0043{"type":"hitsprites",...}"
 *       This lets the client know exactly how many bytes to read before parsing JSON.
 *
 *   'gf' (GF games — Buffalo Thunder, Crab Avengers):
 *       Uses raw JSON with no length prefix — simpler framing.
 *
 * Unauthenticated sockets (ws.player == undefined, e.g. during the login handshake)
 * are treated like 'gf' — plain JSON, no length prefix.
 */

/**
 * Serializes `response` to JSON and sends it to the given WebSocket connection,
 * applying the correct wire format for the player's game type.
 *
 * @param {WebSocket} ws        - The player's WebSocket connection object
 * @param {Object}    response  - The message payload to send (will be JSON.stringify'd)
 *
 * Silently swallows send errors (e.g. disconnected socket) to prevent one bad
 * connection from crashing the server — the error is logged for diagnostics.
 */
async function sendWSMessage(ws, response)
{
    try
    {
        // Unauthenticated socket (not yet logged into a game) — send raw JSON
        if(ws.player == undefined)
        {
            if(response != null)
            {
                var str = JSON.stringify(response);
                ws.send(str);
            }
        }
        // Standard 'gd' game type — length-prefixed protocol
        // Format: "NNNN{...json...}" where NNNN is the 4-digit zero-padded JSON length
        else if (ws.player.gameType == 'gd' || ws.player.gameType == undefined) {
            if(response != null)
            {
                var str = JSON.stringify(response);
                var len = str.length + "";
                str = len.padStart(4, '0') + str; // Prepend 4-char length header
                ws.send(str);
            }
        }
        // 'gf' game type (Buffalo Thunder / Crab Avengers) — raw JSON, no length prefix
        else if (ws.player.gameType == 'gf')
        {
            if(response != null)
            {
                var str = JSON.stringify(response);
                ws.send(str);
            }
        }
    }
    catch(e)
    {
        // Log but do NOT rethrow — a failed send should never crash the game loop
        console.log("websocket send error: " + e);
    }
}

module.exports = {sendWSMessage};