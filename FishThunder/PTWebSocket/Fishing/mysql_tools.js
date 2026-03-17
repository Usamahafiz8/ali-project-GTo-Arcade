/**
 * mysql_tools.js — MySQL Query Helper (Fishing Game Server)
 *
 * Provides a single reusable async wrapper around mysql2's callback-based API,
 * allowing all other modules to use `await` syntax for database queries instead
 * of dealing with nested callbacks.
 *
 * Usage example:
 *   const rows = await mysql_tools.sendQuery(dbconn, "SELECT * FROM w_users WHERE id = ?", [userId]);
 */

const mysql = require('mysql2');

/**
 * Executes a parameterized SQL query against the given connection pool.
 *
 * @param {mysql2.Pool} pool    - The shared connection pool (from DBConn.js)
 * @param {string}      query   - SQL query string (use ? placeholders for safety)
 * @param {Array}       params  - Array of values to substitute for ? placeholders
 * @returns {Promise<Array>}    - Resolves with the result rows array, rejects on SQL error
 *
 * IMPORTANT: Always releases the connection back to the pool after the query,
 * whether it succeeds or fails — prevents connection pool exhaustion.
 */
exports.sendQuery = async (pool, query, params = []) => {
    return new Promise((resolve, reject) => {
         pool.getConnection((err, connection) => {
            connection.query(query, params, (a, b, c) => {
                if (a) {
                    reject(a);   // SQL error — caller should handle/log it
                } else {
                    resolve(b);  // b = result rows array
                }
                connection.release(); // Always return connection to pool
            });
        });
    });
}