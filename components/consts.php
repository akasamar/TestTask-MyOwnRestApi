<?php

const SQL_ADD_USER = 'INSERT INTO users (email,password,access_token,access_expired,refresh_token,refresh_expired) VALUES (?,?,?,?,?,?)';

const SQL_IS_USER_EXIST = 'SELECT COUNT(*) as count FROM users WHERE email = ?';

const SQL_GET_USER_WITH = 'SELECT * FROM users WHERE email = ?';

const SQL_UPDATE_AC_TOKEN = 'UPDATE users SET access_token = ? ,access_expired = ? WHERE email = ?';

const SQL_UPDATE_REF_TOKEN = 'UPDATE users SET refresh_token = ? ,refresh_expired = ? WHERE email = ?';

const SQL_GET_USER_BY_AC_TOKEN = 'SELECT * FROM users WHERE access_token = ?';

const SQL_GET_USER_BY_REF_TOKEN = 'SELECT * FROM users WHERE refresh_token = ?';

const SQL_GET_MAX_ID = 'SELECT MAX(id) as max FROM tasks';

const SQL_CREATE_TASK = 'INSERT INTO tasks (title, priority, user_id) VALUES (?,?,?)';

const SQL_JOIN_TABLES = 'SELECT tasks.id, title, priority, mark_done, `date` FROM users INNER JOIN tasks ON email = ? AND user_id = users.id';

const SQL_GET_USER_BY_TOKEN = 'SELECT * FROM users WHERE access_token = ?';

const SQL_UPDATE_TOKENS = 'UPDATE users SET access_token = ?, refresh_token = ? WHERE access_token = ?';

const SQL_DELETE_TASK = 'DELETE FROM tasks WHERE id = ?';

const SQL_GET_TASK_BY_ID = 'SELECT * FROM tasks WHERE id = ?';

const SQL_UPDATE_MARK = 'UPDATE tasks SET mark_done = 1 WHERE id = ?';