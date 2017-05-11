<?php
namespace Dachande\Mpc;

class Mpc
{
    // Responses
    const RES_OK = "OK";
    const RES_ERR = "ACK";

    // States
    const STATE_PLAYING = "play";
    const STATE_STOPPED = "stop";
    const STATE_PAUSED = "pause";

    // Commands
    const CMD_PWD = "password";
    const CMD_STATS = "stats";
    const CMD_STATUS = "status";
    const CMD_PLIST = "playlistinfo";
    const CMD_CONSUME = "consume";
    const CMD_XFADE = "crossfade";
    const CMD_RANDOM = "random";
    const CMD_REPEAT = "repeat";
    const CMD_SETVOL = "setvol";
    const CMD_SINGLE = "single";
    const CMD_NEXT = "next";
    const CMD_PREV = "previous";
    const CMD_PAUSE = "pause";
    const CMD_PLAY = "play";
    const CMD_PLAYID = "playid";
    const CMD_SEEK = "seek";
    const CMD_SEEKID = "seekid";
    const CMD_STOP = "stop";
    const CMD_PL_ADD = "add";
    const CMD_PL_ADDID = "addid";
    const CMD_PL_CLEAR = "clear";
    const CMD_PL_DELETEID = "deleteid";
    const CMD_PL_MOVE = "move";
    const CMD_PL_MOVE_MULTI = "move";
    const CMD_PL_MOVE_ID = "moveid";
    const CMD_PL_SHUFFLE = "shuffle";
    const CMD_DB_DIRLIST = "lsinfo";
    const CMD_DB_LIST = "list";
    const CMD_DB_SEARCH = "search";
    const CMD_DB_COUNT = "count";
    const CMD_DB_UPDATE = "update";
    const CMD_CURRENTSONG = "currentsong";
    const CMD_LISTPLAYLISTS = "listplaylists";
    const CMD_PLAYLISTINFO = "listplaylistinfo";
    const CMD_PLAYLISTLOAD = "load";
    const CMD_PLAYLISTADD = "playlistadd";
    const CMD_PLAYLISTCLEAR = "playlistclear";
    const CMD_PLAYLISTDELETE = "playlistdelete";
    const CMD_PLAYLISTMOVE = "playlistmove";
    const CMD_PLAYLISTRENAME = "rename";
    const CMD_PLAYLISTREMOVE = "rm";
    const CMD_PLAYLISTSAVE = "save";
    const CMD_CLOSE = "close";
    const CMD_KILL = "kill";
}
