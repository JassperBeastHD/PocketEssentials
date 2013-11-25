==============================================================================================
                                     PocketMine Essentials Package
                                                by Kevin Wang
----------------------------------------------------------------------------------------------
                                     Package Version: 3.5.2-Beta
----------------------------------------------------------------------------------------------
Skype: kvwang98 ( The one without _rec after the username )
E-Mail: kevin@cnkvha.com
----------------------------------------------------------------------------------------------
                                    Join My MCPE Server: 
                                    mcpe.MineConquer.com
==============================================================================================

What's New: 
  - 3.5.2 Beta ( 2013/11/24 )
      - Added a loader to load all things in correct order
  - 3.5.1 Beta ( 2013/11/24 )
      - *(Security)* Fixed GroupManager Command Permission Check Bug
      - GroupManager no longer support OP-Override
           - That means OP system won't work, and you need to add
              commands to your group to make it usable for your group. 
           - Check this link for preset configs: 
              > http://forums.pocketmine.net/index.php?threads/826/
  - 3.5.0 Beta ( 2013/11/24 )
      - No longer require SRC modify! 
      - Added Session Systems (With an API)
         - Server creates a session with player's CID when he/she joins. 
         - Server destory a session when player disconnect. 
         - Support default value
  - 3.4.1 Beta ( 2013/11/2 )
      - Fixed the Rank Time Limit from GroupManager
      - Added a new event: player.afteerjoin
      - Added AutoInstaller for Windows OS
  - 3.4.0 Beta ( 2013/11/1 )
      - Added Time Limit for GroupManager
         You may do "/manuadd <GROUP> <USERNAME> [Days] [ExpireGroup]"
         (If you don't input [Days] and [ExpireGroup] it will be a life-time rank)
      - Added event: pmess.groupmanager.rankexpire
         (See Tutorial_Events.txt)
  - 3.3.3 Alpha (2013/10/30)
      - Made $API->console->run(); more secure 
  - 3.3.2 Alpha ( 2013/10/23 )
      - Fixed /unlock command in ChestLock
  - 3.3.1 Alpha ( 2013/10/20 )
      - Fixed the iControlU Console Spam/Crash bug
  - 3.3.0 Alpha ( 2013/10/20 )
      - Added NoFloatingTrees ( To fix the Tree Drop Bug )
      - Fixed PocketMine-MP Tree Drop Bug ( Now you can get all drops )
      - Fixed Unusual Username Crash server bug
  - 3.2.0 Alpha ( 2013/10/16 )
      - Added Mute Commands ( /mute and /unmute )
      - Released Alpha Version ( Stable Version )
  - 3.1.2 Beta ( 2013/10/15 )
      - Fixed iControlU Uncontrollable PermissionNode
      - Fixed iControlU Server lag and make it more exciting and amazing! 
      - Notice: OP have all PermissionNodes if OP-Override set to true! 
  - 3.1.1 Beta ( 2013/10/15 )
      - Fixed the login bug(/chat-on and /chat-off logic )
  - 3.1.0 Beta ( 2013/10/13 )
      - Added iControlU! 
      - Added 2 Built-In PermissionNodes( See ReadMe_PermissionNodes.txt )
  - 3.0.0 Beta ( 2013/10/12 )
      - Fixed GroupManager OP-Override, set to true to allow OP use any commands in the config
      - Added PermissionNodes into GroupManager
      - Changed the permission format for commands to "&.COMMAND", such as "&.kill"
      - Added some built-in permission nodes for some plugins in the package( See ReadMe_PermissionNodes.txt )
      - Old Version Configs are NOT compatible anymore 
        To fix configs, see "WARNING-Old_Version_Config_Incompatible.txt"
  - 2.3.0 Beta ( 2013/10/11 )
      - Added All Chat Disable ( /chat-on, /chat-off )
      - Fixed the compatible problems between Chest Lock and Protect plugin
      - Improved chat expirence ( Auto New-Line )
  - 2.2.0 Beta ( 2013/10/10 )
      - Added Chest Lock ( Stand on a chest and type /lock or /unlock )
  - 2.1.4 Alpha ( 2013/10/9 )
      - Fixed user can not completely leave a group(GroupManager)
      - Improved the security of Redstone Command Signs
        (It will check permission before running the command)
  - 2.1.3 Alpha ( 2013/10/8 )
      - Some redstone updates, now it is running faster
  - 2.1.2 Alpha ( 2013/10/6 )
      - Fixed InfWorldAPI crash bug 
  - 2.1.1 Alpha ( 2013/10/5 )
      - Pistons now will cause block updates(Sand and gravel will fall)
  - 2.1.0 Beta ( 2013/10/2 )
      - Added ExplosiveBlock to Redstone plugin
  - 2.0.0 Beta ( 2013/10/1 )
      - Added GroupManager system ( Support OP-OverRide Config ) 
      - Added RedStone system
      - Fixed some version bugs in plugin files
  - 1.1.0 Alpha ( 2013/9/19 )
      - Added Home Set commands. (/sethome, /home) 
      - Added Teleport Request commands. (/tpa, /tpaccept, /tpdeny) 
  - 1.0.0 Alpha ( 2013/9/18 )
      - Fixed a lot of known bugs. 
  - 1.0.0 Beta ( 2013/9/14 )
      - First Release
