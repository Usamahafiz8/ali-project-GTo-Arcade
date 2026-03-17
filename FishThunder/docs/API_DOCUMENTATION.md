# FishThunder Platform — Complete API Reference

> **Platform:** VanguardLTE / FishThunder Casino
> **Base Domains:** `www.localhost` (frontend), `admin.localhost` (backend), no domain (REST API)
> **API Auth:** JWT Bearer token (obtained from `POST /api/login`)
> **Web Auth:** Laravel session cookie
> **Generated:** 2026-03-17

---

## Table of Contents

1. [Public REST API — JWT Auth (`/api/*`)](#1-public-rest-api--jwt-auth-api)
2. [Authentication — Frontend & Backend Login/Logout](#2-authentication--frontend--backend-loginlogout)
3. [User Management — Admin Backend](#3-user-management--admin-backend)
4. [Game Management — Admin Backend](#4-game-management--admin-backend)
5. [Game Server Bridge — Game Relay](#5-game-server-bridge--game-relay)
6. [Shop Management](#6-shop-management)
7. [Balance & Credits](#7-balance--credits)
8. [Transactions & Statistics](#8-transactions--statistics)
9. [Jackpots & JPG (Progressive Jackpots)](#9-jackpots--jpg-progressive-jackpots)
10. [Happy Hours](#10-happy-hours)
11. [Bonuses & Welcome Bonuses](#11-bonuses--welcome-bonuses)
12. [Terminal / ATM (V2)](#12-terminal--atm-v2)
13. [Payment Webhooks](#13-payment-webhooks)
14. [Settings & Configuration](#14-settings--configuration)
15. [Roles & Permissions](#15-roles--permissions)
16. [Profile Management](#16-profile-management)
17. [Posts, Articles & Rules](#17-posts-articles--rules)
18. [SMS & Notifications](#18-sms--notifications)
19. [Categories](#19-categories)
20. [Progress & Loyalty](#20-progress--loyalty)
21. [API Key Management](#21-api-key-management)
22. [Security & Sessions](#22-security--sessions)
23. [Dashboard & Statistics — Admin](#23-dashboard--statistics--admin)
24. [Frontend Game Browse Pages](#24-frontend-game-browse-pages)

---

## 1. Public REST API — JWT Auth (`/api/*`)

All `/api/*` routes are defined in `routes/api.php`. The `POST /api/login` route is wrapped in `ipcheck` middleware. All other routes use token-based auth (either JWT Bearer or manual `username`+`token` fields in body).

---

### 1.1 Login

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/login` |
| Domain | any (no domain prefix) |
| Auth | No (ipcheck middleware only) |
| Body | `username` (string, required), `password` (string, required) |
| Success | 200 `{"token": "eyJ..."}` |
| Errors | 401 Invalid credentials, 401 Account blocked/banned, 500 Could not create token |
| Notes | Stores JWT in user's `api_token` column. Supports login by username or email. |

---

### 1.2 Logout

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/logout` |
| Domain | any |
| Auth | No (validates token manually) |
| Body | `username` (string, required), `token` (string, required) |
| Success | 200 `{"success": true}` |
| Errors | 403 Invalid authorization |
| Notes | Clears the `api_token` field for the user. |

---

### 1.3 Get Game List

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/gamelist` |
| Domain | any |
| Auth | Token validated manually via `username`+`token` body fields |
| Body | `username` (string, required), `token` (string, required) |
| Success | 200 `{"slots": [...], "fishes": [...], "firelinks": [...]}` |
| Errors | 200 `{"status":"failure","error":"invalid authroization"}` |
| Notes | Returns games grouped by category_temp: 1=firelink, 2=fish, 3=slot. Each game object includes `id`, `src`, `size`, `tag`, `url`, `orientation`. |

---

### 1.4 Jackpot Status & Bonus Info

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/info` |
| Domain | any |
| Auth | Token validated manually via `username`+`token` body fields |
| Body | `username` (string, required), `token` (string, required) |
| Success | 200 `{"status":"success","content":[{"name":"bronze","jackpot":1234.56},...], "jackpots":[...], "cashback":[...], "reward_available":false, "user_balance":100.00, "i":1}` |
| Errors | 200 `{"status":"failure","error":"invalid authroization"}` |
| Notes | Also updates Redis `player_time_{user_id}` for online tracking. Returns up to 4 jackpot levels (bronze/silver/gold/platinum), recent JPG wins (last 1 min), recent cashback wins, and reward bonus availability. |

---

### 1.5 Change Password

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/changePassword` |
| Domain | any |
| Auth | Token validated manually via `username`+`token` body fields |
| Body | `username` (string, required), `token` (string, required), `old_password` (string, required), `password` (string, required), `password_confirmation` (string, required) |
| Success | 200 `{"success": true}` |
| Errors | 403 Invalid authorization, 403 Password confirmation wrong, 422 `{"error":"The password is incorrect"}` |

---

### 1.6 Get Reward (Wheel Bonus)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/reward` |
| Domain | any |
| Auth | Token validated manually via `username`+`token` body fields |
| Body | `username` (string, required), `token` (string, required), `pick` (integer 0–4, chosen slot index) |
| Success | 200 `{"status":"success","pick_items":[30,10,20,30,10],"picked_index":2,"win_amount":50}` |
| Errors | 403 Reward bonus not available |
| Notes | Picks from 5 random percentage awards (10%–30% of `reward_base`). Adds win to balance and doubles to `reward_bonus`. Resets `reward_base` to 0. |

---

### 1.7 Player Sign-Up (Demo)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/signup` |
| Domain | any |
| Auth | No |
| Body | `username` (string, 6–16 chars, alphanumeric/dash), `password` (string, 6–16), `email` (string, valid email, unique), `phone` (string, exactly 10 digits) |
| Success | 200 `{"status":"success","message":"Free user created successfully"}` |
| Errors | 200 `{"result":"error","message":"validation error text"}`, 200 `{"result":"failure","message":"no demo shop"}` |
| Notes | Creates a player (role_id=1) on the demo shop (is_demo=1) with starting balance=5. |

---

### 1.8 List Users (API)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/users` |
| Domain | any |
| Auth | JWT Bearer |
| Query | `search` (username partial), `status` (Active/Banned/Unconfirmed), `role` (role_id integer) |
| Success | 200 Paginated user list (UserTransformer), 50 per page |
| Notes | Scope filtered by caller's role. Admin sees agents/distributors; agent sees distributors; distributor sees managers; cashier/shop-level sees shop users. |

---

### 1.9 Create User (API)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/users` |
| Domain | any |
| Auth | JWT Bearer |
| Body | `username` (string, unique), `password` (string), `balance` (number, optional — adds to new user if role=User) |
| Success | 201 User object (UserTransformer) |
| Errors | 403 No permission / Max 10000 users / Not enough money in shop / Shift not opened |
| Notes | Creates a user one role level below the caller. Automatically attaches the appropriate role. |

---

### 1.10 Mass Create Users (API)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/users/mass` |
| Domain | any |
| Auth | JWT Bearer (cashier role only) |
| Body | `count` (integer, 1–100), `balance` (number per user) |
| Success | 200 `{"success": true}` |
| Errors | 403 Max 100 per request / Not enough shop balance / Shift not opened |
| Notes | Generates random 9-digit username/password for each player. |

---

### 1.11 Show User (API)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/users/{user}` |
| Domain | any |
| Auth | JWT Bearer |
| Success | 200 User object (UserTransformer) |
| Errors | 403 No permission |

---

### 1.12 Update User (API)

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/api/users/{user}` |
| Domain | any |
| Auth | JWT Bearer |
| Body | `username` (string, unique), `email` (string, nullable), `password` (string, optional), `password_confirmation` (string, optional), `status` (string) |
| Success | 200 Updated user object (UserTransformer) |
| Errors | 403 No permission |

---

### 1.13 Delete User (API)

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/api/users/{user}` |
| Domain | any |
| Auth | JWT Bearer |
| Success | 200 `{"success": true}` |
| Errors | 403 Balance not zero / Has child users / Has activity stats |
| Notes | Cascades deletion of statistics, game logs, sessions, shop user links. |

---

### 1.14 List Games (API)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/games` |
| Domain | any |
| Auth | JWT Bearer (user role only) |
| Query | `id` (game_id), `name` (pipe-separated names), `search` (title keyword), `device` (pipe-separated: 0/1/2), `view` (0/1), `labels` (pipe-separated), `category` (pipe-separated category IDs), `subcat` (new/hot) |
| Success | 200 Paginated game list (GameTransformer) |
| Notes | Default page size 25; if category specified, returns up to 1,000,000 records. |

---

### 1.15 List Jackpots (API)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/jackpots` |
| Domain | any |
| Auth | JWT Bearer |
| Query | `id` (pipe-separated IDs), `search` (name keyword) |
| Success | 200 Paginated jackpot list (JackpotTransformer), up to 100,000 |

---

### 1.16 List Shops (API)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/shops` |
| Domain | any |
| Auth | JWT Bearer (not cashier role) |
| Query | `name`, `credit_from`, `credit_to`, `frontend`, `percent_from`, `percent_to`, `order`, `currency`, `status`, `categories[]` (array), `users` (user_id) |
| Success | 200 Paginated shop list (ShopTransformer), 20 per page |

---

### 1.17 Create Shop (API)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/shops` |
| Domain | any |
| Auth | JWT Bearer (distributor role only) |
| Body | `name` (string, unique, required), `currency` (string, required), `percent` (string, required), `orderby` (string, required), `country[]` (array, required), `os[]` (array, required), `device[]` (array, required), `access` (required), `frontend` (string), `max_win` (number), `categories[]` (array), `balance` (number) |
| Success | 201 Shop object (ShopTransformer) |
| Errors | 422 Validation error, 422 Max shops (1000) reached |

---

### 1.18 Get Shop (API)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/shops/{shop_id}` |
| Domain | any |
| Auth | JWT Bearer (not cashier/user) |
| Success | 200 Shop object (ShopTransformer) |
| Errors | 404 Not found / no access |

---

### 1.19 Update Shop (API)

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/api/shops/{shop}` |
| Domain | any |
| Auth | JWT Bearer (not cashier/user) |
| Body | `name`, `frontend`, `currency`, `percent`, `max_win`, `orderby`, `is_blocked` (0/1), `country[]`, `os[]`, `device[]`, `access`, `categories[]` |
| Success | 201 Updated shop object (ShopTransformer) |

---

### 1.20 Admin Create Full Shop (API)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/shops/admin` |
| Domain | any |
| Auth | JWT Bearer (admin only) |
| Body | `name`, `percent`, `frontend`, `orderby`, `max_win`, `currency`, `balance` (float), `country[]`, `os[]`, `device[]`, `access`, `categories[]`, `agent{username,password,balance}`, `distributor{username,password,balance}`, `manager{username,password}`, `cashier{username,password}`, `users{count,balance}` |
| Success | 200 Shop object (ShopTransformer) |
| Notes | Creates a full shop hierarchy in one request: admin → agent → distributor → manager → cashier → N players with initial balances and an open shift. |

---

### 1.21 Get Shop Currency (API)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/shops/currency` |
| Domain | any |
| Auth | JWT Bearer |
| Success | 200 `{"currency": "USD"}` |

---

### 1.22 Add/Remove Shop Balance (API)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/shops/{shop}/balance/{type}` |
| Domain | any |
| Auth | JWT Bearer (distributor only) |
| URL params | `type`: `add` or `out` |
| Body | `summ` (number, required) |
| Success | 200 `{"success": true}` |
| Errors | 422 Not enough money in user/shop balance |

---

### 1.23 Block Shop (API)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/shops/block` |
| Domain | any |
| Auth | JWT Bearer (permission: shops.block) |
| Success | 200 `{"success": true}` |
| Notes | Invalidates all player (role=1) sessions in the shop. |

---

### 1.24 Unblock Shop (API)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/shops/unblock` |
| Domain | any |
| Auth | JWT Bearer (permission: shops.unblock) |
| Success | 200 `{"success": true}` |

---

### 1.25 Delete Shop (API)

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/api/shops/{shop}` |
| Domain | any |
| Auth | JWT Bearer (admin or agent) |
| Success | 200 `{"success": true}` |
| Errors | 422 Users/games/jackpots/pincodes still have balance |

---

### 1.26 List Transactions (API)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/transactions` |
| Domain | any |
| Auth | JWT Bearer |
| Query | `user` (username), `system` (shop/user/jpg/bank etc), `role` (role_id), `credit_in_from`, `credit_in_to`, `credit_out_from`, `credit_out_to`, `money_in_from`, `money_in_to`, `money_out_from`, `money_out_to`, `dates` ("Y-m-d H:i:s - Y-m-d H:i:s"), `shifts` (shift_id) |
| Success | 200 Paginated statistics (StatisticTransformer), 100 per page |

---

### 1.27 Player Check Login

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/player/login` |
| Domain | any |
| Auth | No |
| Body | `username` (string, required), `password` (string, required) |
| Success | 200 User status data (StatusTransformer) with JWT token stored in `api_token` |
| Errors | 200 `{"success":false,"errormsg":"Login Fail, please check username"}` |

---

### 1.28 Player Login Sync Check

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/player/login/syn` |
| Domain | any |
| Auth | No |
| Body | `username` (string, required), `password` (string, required) |
| Success | 200 `{"status":true,"errormsg":"","login":true}` |
| Errors | 200 `{"status":false,"errormsg":"Login Fail...","login":false}` |

---

### 1.29 Check User Online Status

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/player/online` |
| Domain | any |
| Auth | No |
| Body | `userid` (integer, required) |
| Success | 200 `{"success":true,"data":{"is_online":true,"player_score":150.00,"idleseconds":30.5}}` |
| Notes | Online if `last_online` was within 5 minutes. |

---

### 1.30 Get User Data by Username

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/player/data` |
| Domain | any |
| Auth | No |
| Query | `username` (string, required) |
| Success | 200 `{"success":true,"data":{"player_score":150.00}}` |
| Errors | 200 `{"success":false,"errormsg":"Invalid Username"}` |

---

### 1.31 Check User Score by ID

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/player/score` |
| Domain | any |
| Auth | No |
| Query | `userid` (integer, required) |
| Success | 200 `{"success":true,"data":{"player_score":150.00}}` |
| Errors | 200 `{"success":false,"errormsg":"Invalid User Id"}` |

---

### 1.32 Load Shop Balance (Sum of children)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/player/shop-balance` |
| Domain | any |
| Auth | No |
| Query | `parent_id` (integer, required) |
| Success | 200 `{"success":true,"balance":5000.00}` |

---

### 1.33 Load Total In Amounts

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/player/in-amounts` |
| Domain | any |
| Auth | No |
| Query | `parent_id` (integer, required) |
| Success | 200 `{"success":true,"player_score":10000.00}` |

---

### 1.34 List Credits

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/api/player/credits` |
| Domain | any |
| Auth | No |
| Success | 200 `{"status":true,"errormsg":"","data":[...credit records...]}` |

---

### 1.35 Deposit Credits via Ticket

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/player/credits/deposit` |
| Domain | any |
| Auth | No (userhash via body) |
| Body | `userhash` (string, required — user's `auth_token`), `amount` (number, required), `ts_id` (integer, ticket_id, required) |
| Success | 200 `{"status":true,"errormsg":""}` |
| Errors | 200 `{"status":false,"errormsg":"Please check Ticket ID or Amount"}` |
| Notes | Validates pay ticket status=0 (pending) then marks it status=1 (completed). |

---

### 1.36 Pending Cash-In Check

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/player/credits/pending` |
| Domain | any |
| Auth | No (userhash via body) |
| Body | `userhash` (string, required), `amount` (number, required), `ts_id` (integer, required) |
| Success | 200 `{"status":true,"errormsg":""}` |
| Errors | 200 `{"status":false,"errormsg":"Please check Ticket ID or Amount"}` |

---

### 1.37 Create Payout Ticket

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/player/ticket` |
| Domain | any |
| Auth | No (userhash via body) |
| Body | `userhash` (string, required), `amount` (number, required) |
| Success | 200 `{"success":true,"data":{"ticket_id":1,"ticket_pin":"TEST_TICKET","ticket_amount":100,"ticket_date":"2026/03/17"}}` |
| Errors | 200 `{"success":false,"errormsg":"Access Denied!!"}` |
| Notes | Only callable by cashier role (validated via userhash). |

---

### 1.38 V3 Deposit Credits

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/v3/credits/deposit` |
| Domain | any |
| Auth | No (userhash via JSON body) |
| Body | JSON: `{"userhash":"<auth_token>","amount":100}` |
| Success | 200 `{"status":true,"msg":"credit has been deposited successfully!"}` |
| Errors | 200 `{"status":false,"msg":"userhash should not be blank!"}` or `{"status":false,"msg":"user not found!"}` |
| Notes | Adds to `balance`, `count_balance`, and `total_in`. |

---

### 1.39 V3 Withdraw Credits

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/v3/credits/withdraw` |
| Domain | any |
| Auth | No (userhash via JSON body) |
| Body | JSON: `{"userhash":"<auth_token>","amount":100}` |
| Success | 200 `{"status":true,"msg":"Amount has been withdrawn successfully!"}` |
| Notes | Subtracts from `balance`, adds to `count_balance` and `total_out`. |

---

### 1.40 V3 Withdraw and Cash Out (Zero Balance)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/v3/credits/cashout` |
| Domain | any |
| Auth | No (userhash via JSON body) |
| Body | JSON: `{"userhash":"<auth_token>","amount":100}` |
| Success | 200 `{"status":true,"msg":"All amount has been withdrawn successfully!"}` |
| Notes | Sets `balance` and `count_balance` to 0, adds `amount` to `total_out`. |

---

### 1.41 V3 Generate Payout Ticket

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/v3/credits/ticket` |
| Domain | any |
| Auth | No (userhash via JSON body) |
| Body | JSON: `{"userhash":"<auth_token>","amount":100}` |
| Success | 200 `{"status":true,"msg":"Payout ticket has been generated successfully!","payload":{"user_id":1,"ticket_amount":100,"ticket_pin":"RAND13CHAR","ticket_status":1}}` |

---

### 1.42 V2 ATM Dispatch (Single Endpoint Router)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/api/v2/atm` |
| Domain | any |
| Auth | No |
| Body | JSON: `{"controller":"atm","action":"ping"}` |
| Actions | `atm.ping` → ATM ping/config; `atm.readterminals` → terminal list; `player.readcredits` → player credits |
| Success (atm.ping) | 200 `{"success":"true","data":{"atm_id":"...","atm_name":"","atm_in":"0","atm_out":"0","atm_enabled":"1",...}}` |
| Success (atm.readterminals) | 200 `{"success":"true","data":[{"id":"5540","name":"Math","terminal":"Mestlux","score":"9500"},...]}` |
| Success (player.readcredits) | 200 `{"success":"true","data":{"player_id":"5540","player_name":"","player_score":"9500"}}` |

---

## 2. Authentication — Frontend & Backend Login/Logout

### 2.1 Frontend — Show Login Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/login` |
| Domain | `www.localhost` |
| Auth | Guest only (redirects authenticated users) |
| Success | 200 HTML login page |

---

### 2.2 Frontend — Post Login

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/login` |
| Domain | `www.localhost` |
| Auth | Guest only |
| Body | `username` (string, required), `password` (string, required), `lang` (string, optional), `to` (redirect URL, optional) |
| Success | 302 Redirect to `/` or `?to=` destination |
| Errors | 302 Redirect back with errors (invalid credentials, blocked, banned, shop pending, email unconfirmed) |
| Notes | Supports login throttling (configurable max attempts). Updates user language if `lang` provided. |

---

### 2.3 Frontend — Quick Signup (Demo Account)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/signup` |
| Domain | `www.localhost` |
| Auth | No |
| Body | `username` (string, 6–16 alphanumeric/dash, unique), `password` (string, 6–16), `email` (string, valid email, unique), `phone` (string, exactly 10 digits) |
| Success | 200 JSON `{"status":"success","message":"Free user created successfully"}` |
| Errors | 200 JSON `{"result":"error","message":"...validation message..."}` |

---

### 2.4 Frontend — Logout

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/logout` |
| Domain | `www.localhost` |
| Auth | Required |
| Success | 302 Redirect to `/` |
| Notes | If an admin was logged in as a player (`beforeUser` session var), returns to admin session instead. |

---

### 2.5 Frontend — Special Token Auth (URL-based login)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/specauth/{user}` |
| Domain | `www.localhost` |
| Auth | No |
| URL params | `user` (user ID) |
| Query | `token` (string, must match user's `auth_token`) |
| Success | 302 Redirect to game list (user role) or backend (other roles) |
| Errors | 302 Redirect to login with no-permission error |

---

### 2.6 Frontend — API Game Launcher (Token Login + Game Redirect)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/launcher/{game}/{token}` |
| Domain | `www.localhost` |
| Auth | No |
| URL params | `game` (game name), `token` (user's `api_token`) |
| Success | 302 Redirect to `/game/{game}?api_exit={HTTP_REFERER}` |
| Notes | Logs out any current session, then authenticates via api_token. |

---

### 2.7 Frontend — Show Register Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/register` |
| Domain | `www.localhost` |
| Auth | Guest only |
| Success | 200 HTML registration form |

---

### 2.8 Frontend — Post Register

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/register` |
| Domain | `www.localhost` |
| Auth | Guest only (registration middleware) |
| Body | `email` (required, unique), `username` (required), `password` (required) |
| Success | 302 Redirect to login with success message |

---

### 2.9 Frontend — Confirm Email

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/register/confirmation/{token}` |
| Domain | `www.localhost` |
| Auth | No |
| URL params | `token` (email confirmation token) |
| Success | 302 Redirect to `/` with success message, sets status=Active |
| Errors | 302 Redirect to `/` with wrong-token error |

---

### 2.10 Backend — Show Login Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/login` |
| Domain | `admin.localhost` |
| Auth | No |
| Success | 200 HTML admin login page |

---

### 2.11 Backend — Post Login

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/login` |
| Domain | `admin.localhost` |
| Auth | No |
| Body | `username` (string, required), `password` (string, required) |
| Success | 302 Redirect to dashboard or user list |

---

### 2.12 Backend — Logout

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/logout` |
| Domain | `admin.localhost` |
| Auth | Required |
| Success | 302 Redirect to `/login` |

---

## 3. User Management — Admin Backend

All backend routes are on domain `admin.localhost` and require session authentication.

### 3.1 List Users

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.manage` |
| Success | 200 HTML hierarchical user list |
| Notes | Shows parent-child tree with role-appropriate filtering. Manager's balance shown as shop balance. |

---

### 3.2 User Hierarchy Tree

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/tree` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.tree` |
| Success | 200 HTML tree visualization |

---

### 3.3 Get User Balances (AJAX JSON)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/balances` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 JSON `{"<user_id>": {"balance":"100.00","shop_limit":1000}, ...}` |

---

### 3.4 Search Users

| Field | Value |
|-------|-------|
| Method | GET / POST |
| URL | `/search_user/` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.manage` |
| Query/Body | `search` (string) |
| Success | 200 HTML search results |

---

### 3.5 Create User Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` |
| Success | 200 HTML user creation form |

---

### 3.6 Store New User

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` |
| Body | `Users[type]` (agent/manager/cashier), `Users[username]`, `Users[password]`, `Users[password_confirmation]`, `Users[balance]`, `Users[email]`, `Users[name]`, `Users[country]`, `Users[city]`, `Users[phone]`, `Users[status]` (active/disabled), `Users[time_zone]`, `Users[rtp_type]`, `Users[categories][]` (for manager type) |
| Success | 200 JSON `{"status":"success","message":"User Created Successfully"}` |
| Errors | 200 JSON `{"status":"fail","message":"...error..."}` |
| Notes | Creating a `manager` type also creates a new shop with all games/jackpots cloned from template (shop_id=0). Balance transfer uses shop finance system. |

---

### 3.7 View User Detail

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/{user}/show` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML user detail view with activity |

---

### 3.8 Edit User Profile Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/{user}/profile` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.edit` |
| Success | 200 HTML edit form |

---

### 3.9 Update User Details

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/{user}/update/details` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.edit` |
| Body | User detail fields |
| Success | 302 Redirect with success |

---

### 3.10 Update User Login Details

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/user/{user}/update/login-details` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.edit` |
| Body | `username`, `email`, `password`, `password_confirmation` |
| Success | 302 Redirect with success |

---

### 3.11 Soft Delete User

| Field | Value |
|-------|-------|
| Method | DELETE / POST |
| URL | `/user/{user}/delete` or `/user/delete/{user}` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.delete` |
| Success | 302 Redirect |

---

### 3.12 Hard Delete User

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/user/{user}/hard_delete` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.delete` |
| Success | 302 Redirect |

---

### 3.13 Toggle User Block Status

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/toggle/{user}` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 JSON |

---

### 3.14 Kickout User (Force Logout)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/kickout/{user}` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 JSON |

---

### 3.15 Update User Balance

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/balance/update` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `user_id` (integer), `amount` (number), `type` (add/out) |
| Success | 302 Redirect |

---

### 3.16 Update User Shop Limit

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/limit/update` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `user_id` (integer), `limit` (number) |
| Success | 302 Redirect |

---

### 3.17 Create Cashier (Manager only)

| Field | Value |
|-------|-------|
| Method | GET / POST |
| URL | `/user/cashier_create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` (manager role) |
| Body (POST) | `Users[username]`, `Users[password]`, `Users[password_confirmation]` |
| Success | 200 HTML / 302 Redirect |

---

### 3.18 Edit Cashier

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/cashier_edit/{user}` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` (manager, must be parent) |
| Success | 200 HTML |

---

### 3.19 Update Cashier

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/cashier_update/{user}` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` |
| Body | `Users[password]`, `Users[password_confirmation]` |
| Success | 302 Redirect |

---

### 3.20 Player Create Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/player_create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` |
| Success | 200 HTML player creation page with search |

---

### 3.21 Store New Player

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/player_create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` |
| Body | `Accounts[name]` (min 4 chars), `Accounts[username]` (6–16 alphanumeric/dash), `Accounts[password]` (6–16), `Accounts[balance]` (integer 0–9999999) |
| Success | 302 Redirect with success |
| Errors | 302 Redirect with validation errors or insufficient shop balance |

---

### 3.22 Player Table (AJAX Pagination)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/player_table` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` |
| Body | `search` (string, optional), `page_num` (integer) |
| Success | 200 HTML table partial — includes Redis-based online status per player |

---

### 3.23 Mass Add Players

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/mass` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:users.add` |
| Body | `count` (integer, max 10,000,000 cumulative), `balance` (number) |
| Success | 302 Redirect |
| Errors | 302 Not enough shop balance / Shift not opened |

---

### 3.24 Player History

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/history` |
| Domain | `admin.localhost` |
| Auth | Session |
| Query | `user_id`, date range filters |
| Success | 200 HTML |

---

### 3.25 Game Logs

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/gamelogs` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 3.26 User Transactions

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/transactions` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 3.27 User Statistics

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/statistics` or `/user/{user}/stat` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 3.28 User Report

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/report` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 3.29 Daily Report

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/daily_report` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 3.30 SpecAuth as User (Admin Impersonate)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/{user}/specauth` |
| Domain | `admin.localhost` |
| Auth | Session |
| Notes | Saves current admin ID in `beforeUser` session, then logs into the target user session. |

---

### 3.31 Back Login (Return from Impersonation)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/back_login` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 302 Redirect to backend dashboard |

---

### 3.32 Change Password Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/changePassword` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 3.33 Update Password

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/changePassword` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `old_password`, `password`, `password_confirmation` |
| Success | 302 Redirect |

---

### 3.34 Update Profile (AJAX)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/updateprofile` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | Profile fields |
| Success | 200 JSON |

---

### 3.35 Timezone Data

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/timezone` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 JSON timezone list |

---

### 3.36 Is Connected Check

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/isconnected` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 JSON |

---

### 3.37 Online Users

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/onlineusers` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 JSON list of online users |

---

### 3.38 Send Phone Code

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/user/send_phone_code` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 JSON |

---

### 3.39 User Action (Bulk)

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/user/action/{action}` |
| Domain | `admin.localhost` |
| Auth | Session |
| URL params | `action` (string) |
| Success | 302 Redirect |

---

### 3.40 Update User Avatar

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/{user}/update/avatar` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `avatar` (image file), `points` (crop JSON) |
| Success | 302 Redirect |

---

### 3.41 Update User External Avatar

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/user/{user}/update/avatar/external` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `url` (string, external image URL) |
| Success | 302 Redirect |

---

## 4. Game Management — Admin Backend

### 4.1 List Games

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:games.manage` |
| Query | `search` (keyword), `view` (Active/0=Disabled), `device` (0/1/2), `gamebank`, `label`, `jpg` (jpg_id), `denomination`, `rezerv` (0/1), `category[]` (array), `order` (low/high) |
| Success | 200 HTML game list with aggregate stats (bank, in, out, RTP, slots, fish, bonus) |

---

### 4.2 Games JSON (for dropdowns)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/games.json` |
| Domain | `admin.localhost` |
| Auth | Session |
| Query | `view`, `device`, `categories[]` |
| Success | 200 JSON array of unique game names |

---

### 4.3 Create Game Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game/create` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML |

---

### 4.4 Store New Game

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/create` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | Game fields including `shop_id` (must be in admin's available shops) |
| Success | 302 Redirect |

---

### 4.5 Edit Game Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game/{game}/edit` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML edit form with activity log, last 5 stats |

---

### 4.6 Update Game

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/{game}/update` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | `category` (string), `tag` (string), `rtp` (float, min 0), `view` (0/1) |
| Success | 200 JSON `{"result":"Game setting has been updated"}` |

---

### 4.7 Delete Game

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/game/{game}/delete` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 302 Redirect |

---

### 4.8 Bulk Game Operations (Categories & Values)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/categories` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | `ids` (comma-separated game IDs) OR `games` (newline-separated names), `action` (change_category/add_category/delete_games/stay_games/change_values), `category[]` (for category actions), `all_shops` (checkbox), + game value fields for change_values: `rezerv`, `cask`, `scaleMode`, `numFloat`, `gamebank`, `slotViewState`, `ReelsMath`, `bet`, `view`, `label`, `denomination`, `jpg_id`, `line_spin`, `line_spin_bonus`, `line_bonus`, `line_bonus_bonus` |
| Success | 302 Redirect |

---

### 4.9 Mass Update RTP

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/update/mass` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | `fishing_rtp` (numeric), `slot_rtp` (numeric) |
| Success | 302 Redirect |
| Notes | Updates all fish games (category_temp=2) with fishing_rtp, all others with slot_rtp. |

---

### 4.10 Bulk Enable/Disable Games

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/update/view` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `checkbox[<game_id>]` (array), `action` (enable/disable) |
| Success | 302 Redirect |

---

### 4.11 Clear All Games

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/game/clear` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 302 Redirect (creates a task to clear games on game server) |

---

### 4.12 Toggle Single Game Enable/Disable

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/{game}/switch` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 JSON `{"result":"success"}` |
| Notes | Toggles all games sharing the same `original_id`. |

---

### 4.13 Show Game Detail

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game/{game}/show` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 4.14 Game Win Settings Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game_setting` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML with all games and win setting templates |

---

### 4.15 Get Win Settings for Specific Game

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game_win_setting/{game}` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML win settings form |

---

### 4.16 Update Win Settings

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game_win_setting` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | `id` (setting_id), `game_id`, `type` (0=single game/1=all games), `bsc_min`, `bsc_max`, `bsw_min`, `bsw_max`, `bbc_min`, `bbc_max`, `bbw_min`, `bbw_max`, `fc_min`, `fc_max`, `fw_min`, `fw_max`, `fw_bc_min`, `fw_bc_max`, `fw_bw_min`, `fw_bw_max` |
| Success | 200 JSON `{"result":"success"}` |
| Notes | For type=0, also invalidates Redis cache keys for all shops. |

---

### 4.17 Save Win Settings as Template

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/setting/savetemplate` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | `name` (string, required) |
| Success | 302 Redirect |

---

### 4.18 Load Win Settings Template

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/setting/loadtemplate` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | `template` (template_id integer) |
| Success | 302 Redirect |

---

## 5. Game Server Bridge — Game Relay

### 5.1 Backend Game Launcher

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game/{game}` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML game view (SlotSettings loaded for admin context) |

---

### 5.2 Backend Game Server Relay

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/{game}/server` |
| Domain | `admin.localhost` |
| Auth | Session |
| Notes | Proxies to `\VanguardLTE\Games\{game}\Server::get()` — handles game spin/bet WebSocket relay. |

---

### 5.3 Frontend Game Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game/{game}` |
| Domain | `www.localhost` |
| Auth | Required (user role) |
| Success | 200 HTML game iframe/embed |

---

### 5.4 Frontend Game Direct Open

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game/open/{game}` |
| Domain | `www.localhost` |
| Auth | Required |
| Query | `api_exit` (return URL) |
| Notes | Direct open for API-authenticated players. |

---

### 5.5 Frontend Game Server Relay

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/game/{game}/server` |
| Domain | `www.localhost` |
| Auth | Required |
| Notes | Game server relay for player context. |

---

### 5.6 Frontend Game with Prego Parameter

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game/{game}/{prego}` |
| Domain | `www.localhost` |
| Auth | Required |
| Success | 200 HTML |

---

## 6. Shop Management

### 6.1 List Shops (Backend)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/shops` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Query | `name`, `credit_from`, `credit_to`, `frontend`, `balance_from`, `balance_to`, `percent` |
| Success | 200 HTML shop list |

---

### 6.2 Create Shop Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/shops/create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.add` |
| Success | 200 HTML |

---

### 6.3 Store New Shop

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/shops/create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.add` |
| Body | Shop configuration fields |
| Success | 302 Redirect |

---

### 6.4 Admin Create Shop (Backend)

| Field | Value |
|-------|-------|
| Method | GET / POST |
| URL | `/shops/admin/create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Success | 200 HTML / 302 |

---

### 6.5 Get Demo Shop

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/shops/get_demo` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.free_demo` |
| Success | 302 Redirect |

---

### 6.6 Fast Shop

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/shops/fast_shop` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Success | 200 HTML |

---

### 6.7 Danger Zone

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/shops/danger` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Success | 200 HTML |

---

### 6.8 Edit Shop

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/shops/{shop}/edit` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Success | 200 HTML edit form |

---

### 6.9 Update Shop

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/shops/{shop}/update` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Body | Shop fields |
| Success | 302 Redirect |

---

### 6.10 Shop Balance Transfer

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/shops/balance` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Body | `shop_id` (integer), `amount` (number), `type` (add/out) |
| Success | 302 Redirect |

---

### 6.11 Delete Shop (Soft)

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/shops/{shop}/delete` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.delete` |
| Success | 302 Redirect |

---

### 6.12 Hard Delete Shop

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/shops/{shop}/hard_delete` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.hard_delete` |
| Success | 302 Redirect |

---

### 6.13 Shop Action

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/shops/{shop}/action/{action}` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Success | 302 Redirect |

---

### 6.14 Regenerate Jackpots for Shop

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/shops/{shop}/regenerate_jp` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.manage` |
| Success | 302 Redirect |

---

### 6.15 Block Shop (Backend)

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/shops/block` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.block` |
| Notes | Invalidates all player sessions, sets `is_blocked=1`. |

---

### 6.16 Unblock Shop (Backend)

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/shops/unblock` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:shops.unblock` |
| Success | 302 Redirect |

---

## 7. Balance & Credits

### 7.1 Frontend Balance Add (Cashier Credits Player)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/balance` |
| Domain | `www.localhost` |
| Auth | Session |
| Body | `user_id` (integer), `amount` (number) |
| Success | 302 Redirect |

---

## 8. Transactions & Statistics

### 8.1 Transactions View (Backend)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/transactions` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:stats.pay` |
| Success | 200 HTML |

---

### 8.2 Game Statistics (Backend)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/game_stat` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:stats.game` |
| Success | 200 HTML |

---

### 8.3 Shift Statistics (Backend)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/shift_stat` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:stats.shift` |
| Success | 200 HTML |

---

### 8.4 Start Shift

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/start_shift` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 8.5 Print Shift Report

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/start_shift/print` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML print view |

---

### 8.6 Banks Management

| Field | Value |
|-------|-------|
| Method | GET / POST |
| URL | `/banks` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML |

---

### 8.7 Banks Update Trigger

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/banks/update` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | Bank update parameters |
| Success | 302 Redirect |

---

## 9. Jackpots & JPG (Progressive Jackpots)

### 9.1 List Jackpots (Backend)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/jpgame` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:jpgame.manage` |
| Success | 200 HTML list of jackpots for current shop |

---

### 9.2 Edit Global Jackpot Parameters

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/jp_edit` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML edit form for Bronze/Silver/Gold/Platinum |

---

### 9.3 Update Global Jackpot Parameters

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/jp/updatejp` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:jpgame.edit` |
| Body | `Bronze[start_balance]`, `Bronze[start_payout]`, `Bronze[end_payout]`, `Silver[start_balance]`, `Silver[start_payout]`, `Silver[end_payout]`, `Gold[start_balance]`, `Gold[start_payout]`, `Gold[end_payout]`, `Platinum[start_balance]`, `Platinum[start_payout]`, `Platinum[end_payout]`, `percent` |
| Success | 302 Redirect |
| Notes | Updates all shops' jackpots with new parameters. Resets `balance` to `start_balance`. Generates random `pay_sum` between start_payout and end_payout. |

---

### 9.4 Regenerate Single Jackpot Pay Sum

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/jp/regenerate` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:jpgame.edit` |
| Body | `name` (Bronze/Silver/Gold/Platinum) |
| Success | 200 JSON `{"result":"success"}` |
| Notes | Resets balance to start_balance and randomizes pay_sum. |

---

### 9.5 Edit Individual Jackpot

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/jpgame/{jackpot}/edit` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:jpgame.manage` |
| Success | 200 HTML |

---

### 9.6 Update Individual Jackpot

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/jpgame/{jackpot}/update` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:jpgame.edit` |
| Body | `name`, `pay_sum`, `percent`, `start_balance`, `balance` (admin only — creates Statistic record), `user_id` |
| Success | 302 Redirect |

---

### 9.7 Select Jackpots for Bulk Update

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/jpgame/global` |
| Domain | `admin.localhost` |
| Auth | Session (admin or jpgame.edit) |
| Body | `checkbox[<jackpot_id>]` (array of selected jackpot IDs) |
| Success | 200 HTML confirmation form with selected jackpots |

---

### 9.8 Apply Bulk Jackpot Update

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/jpgame/global_update` |
| Domain | `admin.localhost` |
| Auth | Session (admin or jpgame.edit) |
| Body | `ids` (comma-separated jackpot IDs), `pay_sum`, `percent`, `start_balance`, `balance` (admin only) |
| Success | 302 Redirect |

---

### 9.9 Jackpot History

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/jpg/jackpot_history` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

## 10. Happy Hours

### 10.1 List Happy Hours

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/happyhours` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:happyhours.manage` (shopzero middleware — shop must be set) |
| Success | 200 HTML list |

---

### 10.2 Create Happy Hour Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/happyhours/create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:happyhours.add` |
| Success | 200 HTML form |

---

### 10.3 Store Happy Hour

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/happyhours/create` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:happyhours.add` |
| Body | `multiplier` (required, must be valid wager value), `wager` (required), `time` (must be unique per shop), `status` |
| Success | 302 Redirect (fires NewHappyHour event) |
| Errors | 302 Redirect with unique-time validation error |

---

### 10.4 Edit Happy Hour Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/happyhours/{happyhour}/edit` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML form with activity log |

---

### 10.5 Update Happy Hour

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/happyhours/{happyhour}/update` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `multiplier`, `wager`, `time`, `status` |
| Success | 302 Redirect |

---

### 10.6 Delete Happy Hour

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/happyhours/{happyhour}/delete` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:happyhours.delete` |
| Success | 302 Redirect (fires DeleteHappyHour event) |

---

### 10.7 Toggle Happy Hours Shop Status

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/happyhours/status/{status}` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:happyhours.edit` |
| URL params | `status`: `enable` or `disable` |
| Success | 302 Redirect (updates shop's `happyhours_active` field) |

---

## 11. Bonuses & Welcome Bonuses

### 11.1 Welcome Bonuses List

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/welcome_bonuses` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:welcome_bonuses.manage` |
| Success | 200 HTML |

---

### 11.2 Edit Welcome Bonus

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/welcome_bonuses/{welcome_bonus}/edit` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:welcome_bonuses.edit` |
| Success | 200 HTML |

---

### 11.3 Update Welcome Bonus

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/welcome_bonuses/{welcome_bonus}/update` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:welcome_bonuses.edit` |
| Body | Bonus configuration fields |
| Success | 302 Redirect |

---

### 11.4 Toggle Welcome Bonus Status

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/welcome_bonuses/status/{status}` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 302 Redirect |

---

### 11.5 Bonus Setting (Wheel of Fortune Configuration)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/bonus/setting` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML with 30-slot wheel configuration |

---

### 11.6 Update Bonus Settings

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/bonus/update` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `values` (JSON object, 30 slots indexed 0–29 with credit amounts) |
| Success | 200 JSON `{"result":"success"}` |

---

### 11.7 Bonus Logs

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/bonus/logs` |
| Domain | `admin.localhost` |
| Auth | Session |
| Query | `search` (username), `DateFilterForm[dateFrom]` (MM-DD-YYYY), `DateFilterForm[dateTill]`, `DateFilterForm[timeFrom]` (HH:MM), `DateFilterForm[timeTill]`, `filter` (today/yesterday/week/month) |
| Success | 200 HTML paginated bonus log (20 per page) |

---

### 11.8 Invite Management

| Field | Value |
|-------|-------|
| Method | GET / POST |
| URL | `/invite` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:invite.manage` (shopzero) |
| Success | 200 HTML / 302 |

---

### 11.9 Invite Status Toggle

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/invite/status/{status}` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:invite.edit` |
| Success | 302 Redirect |

---

### 11.10 Wheel of Fortune Management

| Field | Value |
|-------|-------|
| Method | GET / POST |
| URL | `/wheelfortune` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:wheelfortune.manage` (shopzero) |
| Success | 200 HTML / 302 |

---

### 11.11 Wheel of Fortune Status Toggle

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/wheelfortune/status/{status}` |
| Domain | `admin.localhost` |
| Auth | Session + `permission:wheelfortune.manage` |
| Success | 302 Redirect |

---

### 11.12 Frontend Collect Wheel

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/collect_wheel` |
| Domain | `www.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 11.13 Frontend Reward Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/reward` |
| Domain | `www.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

## 12. Terminal / ATM (V2)

### 12.1 Terminal List

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/terminal` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML list of terminals (role_id=7 users) for current shop |

---

### 12.2 Create Terminal

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/terminal/create` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `username` (string), `password` (string), `cpassword` (confirmation), `status` (string) |
| Success | 302 Redirect with success |
| Errors | 302 Redirect with "Password mismatch!" error |

---

### 12.3 Terminal Details View

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/terminal/details/{id}` |
| Domain | `admin.localhost` |
| Auth | Session |
| URL params | `id` (base64-encoded terminal user ID) |
| Success | 200 HTML with user data, activity log, pay tickets |

---

### 12.4 Update Terminal

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/terminal/details/{id}` |
| Domain | `admin.localhost` |
| Auth | Session |
| URL params | `id` (encoded terminal ID) |
| Body | `username`, `status`, `language`, `password` |
| Success | 302 Redirect |

---

### 12.5 Terminal Balance Add (Cash In)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/terminal/balance/add` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `user_id` (integer — terminal user ID), `amount` (number) |
| Success | 302 Redirect with success |
| Errors | 302 "Shop has no balance!" |

---

### 12.6 Terminal Balance Out (Cash Out)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/terminal/balance/out` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `user_id` (integer), `amount` (number) |
| Success | 302 Redirect |
| Errors | 302 "Not enough balance!" |

---

### 12.7 Terminal AJAX Pay Tickets

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/terminal/ajax/pay-tickets` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `terminalId` (integer) |
| Success | 200 JSON array of pay ticket records |

---

### 12.8 ATM List

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/atm` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML list of ATMs for current shop |

---

### 12.9 Create New ATM

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/atm/create` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 302 Redirect (creates ATM record + API key, ATM gets random 10-char name) |

---

### 12.10 Reset ATM Counters

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/atm/reset` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 302 Redirect (clears all atm_in, atm_out, atm_recycle, atm_rec_5/10/20/50/100/200 counters) |

---

### 12.11 ATM Status Update

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/atm/status/{status}` |
| Domain | `admin.localhost` |
| Auth | Session |
| URL params | `status` (base64-encoded 0 or 1) |
| Success | 302 Redirect |

---

### 12.12 Generate New ATM API Key

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/atm/newkey/{api_id}` |
| Domain | `admin.localhost` |
| Auth | Session |
| URL params | `api_id` (encoded API key ID) |
| Success | 302 Redirect |

---

### 12.13 Delete ATM

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/atm/delete/{id}/{api_id}` |
| Domain | `admin.localhost` |
| Auth | Session |
| URL params | `id` (encoded ATM record ID), `api_id` (encoded API key ID) |
| Success | 302 Redirect |

---

## 13. Payment Webhooks

### 13.1 Interkassa IPN Webhook

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/payment/interkassa/result` |
| Domain | `www.localhost` |
| Auth | No (signature verified internally) |
| Body | Interkassa IPN payload |
| Success | 200 |

---

### 13.2 Interkassa Success Redirect

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/payment/interkassa/success` |
| Domain | `www.localhost` |
| Auth | No |
| Success | 200 HTML success page |

---

### 13.3 Interkassa Fail Redirect

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/payment/interkassa/fail` |
| Domain | `www.localhost` |
| Auth | No |
| Success | 200 HTML failure page |

---

### 13.4 Interkassa Wait Redirect

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/payment/interkassa/wait` |
| Domain | `www.localhost` |
| Auth | No |
| Success | 200 HTML pending page |

---

### 13.5 Coinbase Commerce Webhook

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/payment/coinbase/result` |
| Domain | `www.localhost` |
| Auth | No (webhook signature verified via X-CC-Webhook-Signature header) |
| Body | Coinbase Commerce event payload |
| Success | 200 |

---

### 13.6 Coinbase Success Redirect

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/payment/coinbase/success` |
| Domain | `www.localhost` |
| Auth | No |
| Success | 200 HTML |

---

### 13.7 Coinbase Fail Redirect

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/payment/coinbase/fail` |
| Domain | `www.localhost` |
| Auth | No |
| Success | 200 HTML |

---

### 13.8 BtcPayServer Invoice Webhook

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/payment/btcpayserver/result` |
| Domain | `www.localhost` |
| Auth | No (signature verified internally) |
| Body | BTCPay Server invoice notification JSON |
| Success | 200 |

---

### 13.9 BtcPayServer Redirect

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/payment/btcpayserver/redirect` |
| Domain | `www.localhost` |
| Auth | No |
| Success | 302 Redirect to return URL |

---

## 14. Settings & Configuration

### 14.1 Settings Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/settings/{tab}` |
| Domain | `admin.localhost` |
| Auth | Session (admin for most tabs; `permission:settings.payment` for payment tab) |
| URL params | `tab`: `general`, `payment`, `sms`, `mail`, or other tabs |
| Success | 200 HTML settings form for the selected tab |

---

### 14.2 Update Settings

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/settings/{tab}` |
| Domain | `admin.localhost` |
| Auth | Session (admin or `permission:settings.payment` for payment tab) |
| Body | All key-value settings pairs. For `payment` tab: `system[system_name][field_name][shop_id]=value`. For `sms` tab: `smsto_alert_phone`, `smsto_alert_phone_2`. For `general`: `blocked_phone_prefixes`, `blocked_countries`, `blocked_domains`, `siteisclosed` |
| Success | 302 Redirect with success message |
| Notes | `general` tab: re-evaluates all users against new block filters and invalidates blocked sessions. `siteisclosed`: invalidates all non-admin sessions. |

---

### 14.3 Sync Settings (to game servers)

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/settings/sync` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 302 Redirect (creates a sync task) |

---

### 14.4 Delete Game Statistics

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/settings/delete/stat/game` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 302 Redirect |

---

### 14.5 Delete Game Logs

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/settings/delete/log/game` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 302 Redirect |

---

### 14.6 Generator

| Field | Value |
|-------|-------|
| Method | GET / POST |
| URL | `/generator` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 14.7 Securities (IP/Device Blacklist)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/securities` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML security list |

---

### 14.8 Block Security Item

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/securities/{item}/block` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 302 Redirect |

---

### 14.9 Delete Security Item

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/securities/{item}/delete` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 302 Redirect |

---

## 15. Roles & Permissions

### 15.1 List Roles

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/role` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 15.2 Create Role Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/role/create` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 15.3 Store Role

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/role/store` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `name`, `slug`, `description`, `level` |
| Success | 302 Redirect |

---

### 15.4 Edit Role Form

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/role/{role}/edit` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 15.5 Update Role

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/role/{role}/update` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `name`, `slug`, `description`, `level` |
| Success | 302 Redirect |

---

### 15.6 Delete Role

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/role/{role}/delete` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 302 Redirect |

---

### 15.7 List Permissions

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/permission` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Success | 200 HTML permission matrix |

---

### 15.8 Save Role Permissions

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/permission/save` |
| Domain | `admin.localhost` |
| Auth | Session (admin only) |
| Body | Permission assignment matrix |
| Success | 302 Redirect |

---

## 16. Profile Management

### 16.1 Backend Profile Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/profile` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML (shows current admin user's profile) |

---

### 16.2 Backend Balance (AJAX)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/balance` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 JSON `{"balance":"100.00","currency":"USD"}` |
| Notes | Cashier/manager returns shop balance. Others return user balance. |

---

### 16.3 Backend Update Profile Details

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/profile/details/update` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `first_name`, `last_name`, `email`, `phone` (excludes role_id, status, shops) |
| Success | 302 Redirect |

---

### 16.4 Backend Update Avatar

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/avatar/update` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `avatar` (image file, validated), `points` (crop coordinates JSON) |
| Success | 302 Redirect |

---

### 16.5 Backend Update External Avatar

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/avatar/update/external` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `url` (string, external image URL) |
| Success | 302 Redirect |

---

### 16.6 Backend Update Login Details

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/profile/login-details/update` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `username`, `email`, `password`, `password_confirmation` |
| Success | 302 Redirect |

---

### 16.7 Backend Profile Activity

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/profile/activity` |
| Domain | `admin.localhost` |
| Auth | Session |
| Query | `search` (string) |
| Success | 200 HTML activity log, 20 per page |

---

### 16.8 Backend Profile Sessions

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/profile/sessions` |
| Domain | `admin.localhost` |
| Auth | Session (session.database middleware) |
| Success | 200 HTML session list |

---

### 16.9 Backend Invalidate Session

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/profile/sessions/{session}/invalidate` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 302 Redirect |

---

### 16.10 Backend Set Active Shop

| Field | Value |
|-------|-------|
| Method | GET / POST |
| URL | `/profile/setshop` |
| Domain | `admin.localhost` |
| Auth | Session |
| Body | `shop_id` (integer), `to` (redirect URL, optional) |
| Success | 302 Redirect |
| Notes | Changes the admin's currently active shop context. Validates shop is accessible to user. |

---

### 16.11 Frontend Profile Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/profile` |
| Domain | `www.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 16.12 Frontend Update Profile Details

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/details/update` |
| Domain | `www.localhost` |
| Auth | Session |
| Body | Profile fields (not role_id/status) |
| Success | 200 JSON `{"success":"Profile updated successfully"}` |

---

### 16.13 Frontend Update Phone

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/updatePhone` |
| Domain | `www.localhost` |
| Auth | Session |
| Body | `phone` (string) |
| Success | 200 JSON |

---

### 16.14 Frontend Update Password

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/password/update` |
| Domain | `www.localhost` |
| Auth | Session |
| Body | `password`, `password_confirmation`, `current_password` |
| Success | 200 JSON |

---

### 16.15 Frontend Update New Password

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/password/updateNew` |
| Domain | `www.localhost` |
| Auth | Session |
| Body | `password`, `password_confirmation` |
| Success | 200 JSON |

---

### 16.16 Frontend Update Avatar

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/avatar/update` |
| Domain | `www.localhost` |
| Auth | Session |
| Body | `avatar` (image file), `points` (crop JSON) |
| Success | 302 Redirect |

---

### 16.17 Frontend Update External Avatar

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/avatar/update/external` |
| Domain | `www.localhost` |
| Auth | Session |
| Body | `url` (string) |
| Success | 302 Redirect |

---

### 16.18 Frontend Update Login Details

| Field | Value |
|-------|-------|
| Method | PUT |
| URL | `/profile/login-details/update` |
| Domain | `www.localhost` |
| Auth | Session |
| Body | `username`, `email`, `password`, `password_confirmation` |
| Success | 302 Redirect |

---

### 16.19 Frontend — Enable 2FA

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/two-factor/enable` |
| Domain | `www.localhost` |
| Auth | Session |
| Success | 302 Redirect |

---

### 16.20 Frontend — Disable 2FA

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/profile/two-factor/disable` |
| Domain | `www.localhost` |
| Auth | Session |
| Success | 302 Redirect |

---

### 16.21 Frontend — Profile Sessions

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/profile/sessions` |
| Domain | `www.localhost` |
| Auth | Session |
| Success | 200 HTML |

---

### 16.22 Frontend — Invalidate Session

| Field | Value |
|-------|-------|
| Method | DELETE |
| URL | `/profile/sessions/{session}/invalidate` |
| Domain | `www.localhost` |
| Auth | Session |
| Success | 302 Redirect |

---

### 16.23 Frontend — Set Language

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/setlang/{lang}` |
| Domain | `www.localhost` |
| Auth | Session |
| URL params | `lang` (language code: en/es/etc.) |
| Success | 302 Redirect |

---

### 16.24 Frontend — Profile Sub-Pages

| URL | Method | Description |
|-----|--------|-------------|
| `/profile/activity` | GET | Activity log |
| `/profile/refunds` | GET | Refund history |
| `/profile/ajax` | GET | AJAX profile data endpoint |
| `/profile/message` | GET | Player messages |
| `/profile/pincode` | GET | Pincode management |
| `/profile/daily_entry` | GET | Daily bonus entry |
| `/profile/phone` | GET | Phone verification |
| `/profile/code` | GET | Verification code entry |
| `/profile/agree` | GET | Terms agreement |
| `/profile/reward` | GET | Reward/bonus page |
| `/profile/sms` | GET | SMS notification settings |
| `/profile/clear_phone` | GET | Clear phone number |
| `/profile/contact` | POST | Contact/support form |

---

## 17. Posts, Articles & Rules

### 17.1 Articles Management (Backend, admin only)

| URL | Method | Description |
|-----|--------|-------------|
| `/articles` | GET | List all articles |
| `/articles/create` | GET | New article form |
| `/articles/create` | POST | Store article (`title`, `body`, `tags`, etc.) |
| `/articles/{article}/edit` | GET | Edit article form |
| `/articles/{article}/update` | POST | Update article |
| `/articles/{article}/delete` | DELETE | Delete article |

---

### 17.2 Posts Management (Backend)

| URL | Method | Description |
|-----|--------|-------------|
| `/post/poster` | GET | List promotional posters |
| `/post/video` | GET | List video posts |
| `/post/news` | GET | List news posts |
| `/post/notification` | GET | List notifications |
| `/post/addposter` | POST | Create poster (file upload + metadata) |
| `/post/addvideo` | POST | Create video post |
| `/post/addnews` | POST | Create news item |
| `/post/delete` | POST | Delete a post (`post_id` in body) |

---

### 17.3 Rules Management (Backend, admin only)

| URL | Method | Description |
|-----|--------|-------------|
| `/rules` | GET | List all rules |
| `/rules/{rule}/edit` | GET | Edit rule form |
| `/rules/{rule}/update` | POST | Update rule content |

---

## 18. SMS & Notifications

### 18.1 SMS Callback (Provider IPN)

| Field | Value |
|-------|-------|
| Method | POST |
| URL | `/sms/callback` |
| Domain | `www.localhost` |
| Auth | No |
| Body | SMS provider webhook payload |
| Success | 200 |

---

### 18.2 Send SMS

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/sms/send` |
| Domain | `www.localhost` |
| Auth | No |
| Query | `phone` (string), `message` (string) |
| Success | 200 |

---

### 18.3 Verify SMS Code

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/sms/verify` |
| Domain | `www.localhost` |
| Auth | No |
| Query | `code` (string) |
| Success | 200 |

---

### 18.4 Verify Referral via SMS

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/sms/referral` |
| Domain | `www.localhost` |
| Auth | No |
| Success | 200 |

---

## 19. Categories

### 19.1 Category Management (Backend, admin only)

| URL | Method | Auth | Description |
|-----|--------|------|-------------|
| `/category` | GET | admin | List all categories |
| `/category/create` | GET | admin | New category form |
| `/category/create` | POST | admin | Store category (`name`, `slug`, `parent`, `icon`, etc.) |
| `/category/{category}/edit` | GET | admin | Edit category form |
| `/category/{category}/update` | POST | admin | Update category |
| `/category/{category}/delete` | DELETE | admin | Delete category |

---

## 20. Progress & Loyalty

### 20.1 Progress Management (Backend)

| URL | Method | Auth | Description |
|-----|--------|------|-------------|
| `/progress` | GET | `permission:progress.manage` | List progress/loyalty items |
| `/progress/{progress}/edit` | GET | `permission:progress.edit` | Edit progress item form |
| `/progress/{progress}/update` | POST | `permission:progress.edit` | Update progress item (`name`, `min_amount`, `reward_percent`, etc.) |
| `/progress/status/{status}` | GET | `permission:progress.manage` | Toggle progress system status |

---

### 20.2 Frontend Progress Page

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/progress` |
| Domain | `www.localhost` |
| Auth | Session |
| Success | 200 HTML progress/loyalty level view |

---

## 21. API Key Management

### 21.1 API Key Routes (Backend)

| URL | Method | Auth | Description |
|-----|--------|------|-------------|
| `/api` | GET | `permission:api.manage` | List all API keys |
| `/api/create` | GET | `permission:api.add` | New API key form |
| `/api/create` | POST | `permission:api.add` | Store new API key |
| `/api/generate` | GET | session | Generate a new API key value |
| `/api/json` | GET | session | Get API keys as JSON |
| `/api/{api}/edit` | GET | `permission:api.edit` | Edit API key form |
| `/api/{api}/update` | POST | `permission:api.edit` | Update API key settings |
| `/api/balance` | POST | session | Add/remove balance to API key |
| `/api/{api}/delete` | DELETE | `permission:api.delete` | Delete API key |

---

## 22. Security & Sessions

### 22.1 Backend User Sessions

| URL | Method | Description |
|-----|--------|-------------|
| `/user/{user}/sessions` | GET | List user's active sessions |
| `/user/{user}/sessions/{session}/invalidate` | DELETE | Invalidate a specific session |
| `/user/{user}/two-factor/enable` | POST | Enable 2FA for user |
| `/user/{user}/two-factor/disable` | POST | Disable 2FA for user |
| `/profile/two-factor/enable` | POST | Enable 2FA for self (backend) |
| `/profile/two-factor/disable` | POST | Disable 2FA for self (backend) |
| `/user/action/{action}` | DELETE | Bulk user action |

---

## 23. Dashboard & Statistics — Admin

### 23.1 Dashboard Root

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/` |
| Domain | `admin.localhost` |
| Auth | Session |
| Notes | Redirects users (role=user) to logout. Redirects cashier/manager to player_create. Redirects admin/agent to user list. |

---

### 23.2 Search

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/search` |
| Domain | `admin.localhost` |
| Auth | Session |
| Query | `q` (search term) |
| Success | 200 HTML |

---

### 23.3 Netpos Dashboard (Cashier Interface)

| Field | Value |
|-------|-------|
| Method | GET |
| URL | `/netpos` |
| Domain | `admin.localhost` |
| Auth | Session |
| Success | 200 HTML cashier/shop dashboard |

---

## 24. Frontend Game Browse Pages

| URL | Method | Auth | Description |
|-----|--------|------|-------------|
| `/` | GET | Required | Main game lobby |
| `/hub` | GET | Required | Alternative hub view |
| `/categories/{category1}` | GET | Required | Games by top-level category |
| `/categories/{category1}/{category2}` | GET | Required | Games by subcategory |
| `/search` | GET | Required | Game search results page |
| `/search.json` | GET | Required | Game search JSON endpoint |
| `/setpage.json` | GET | Required | Set pagination page cookie |
| `/subsession` | GET | Session | Sub-session handler |
| `/faq` | GET | Required | FAQ page |
| `/bonuses` | GET | Required | Bonuses overview page |
| `/bonus-conditions` | GET | Required | Bonus conditions page |
| `/progress` | GET | Required | Player progress/loyalty levels |
| `/game_stat` | GET | Required | Game statistics for player |
| `/tournaments` | GET | Required | Tournament list |
| `/tournaments/{tournament}` | GET | Required | Tournament detail |
| `/gamelist` | GET | Required | Alternative game list |
| `/policy` | GET | No | Privacy policy page |
| `/new-license` | GET/POST | No | License agreement page |
| `/license-error` | GET | No | License error page |
| `/jpstv/{id?}` | GET | No | Jackpot TV display (for broadcast screens) |
| `/jpstv.json` | GET | No | Jackpot TV JSON data feed |
| `/deleteuser` | GET | No | Player self-delete |
| `/password/change` | GET | No | Change password page |
| `/refresh-csrf` | GET | No | Returns fresh CSRF token |

---

*End of FishThunder Platform Complete API Reference*
