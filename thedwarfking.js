/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * thedwarfking implementation : © <Your name here> <Your email address here>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * thedwarfking.js
 *
 * thedwarfking user interface script
 * 
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter"
],
function (dojo, declare) {
    return declare("bgagame.thedwarfking", ebg.core.gamegui, {
        constructor: function(){
            console.log('thedwarfking constructor');
              
            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;

        },
        
        /*
            setup:
            
            This method must set up the game user interface according to current game situation specified
            in parameters.
            
            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)
            
            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

/////////////////////////////////////////////////////////////////////////////////           
//    _____                      _____        _            
//   / ____|                    |  __ \      | |           
//  | |  __  __ _ _ __ ___   ___| |  | | __ _| |_ __ _ ___ 
//  | | |_ |/ _` | '_ ` _ \ / _ \ |  | |/ _` | __/ _` / __|
//  | |__| | (_| | | | | | |  __/ |__| | (_| | || (_| \__ \
//   \_____|\__,_|_| |_| |_|\___|_____/ \__,_|\__\__,_|___/
//                                                        
/////////////////////////////////////////////////////////////////////////////////
        
        setup: function( gamedatas )
        {
            console.log( "Starting game setup" );

                       
            // TODO: Set up your game interface here, according to "gamedatas"

            this.players = gamedatas.players; // A RAJOUTER POUR MOTEUR (UTILITY METHODS)
            
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();


            //// CONNECTIONS CLICK
            dojo.query(".carre").connect('onclick', this, 'onSelect' )
            

            console.log( "Ending game setup" );
        },

/////////////////////////////////////////////////////////////////////////////////   
//         _____ _        _            
//        / ____| |      | |           
//       | (___ | |_ __ _| |_ ___  ___ 
//        \___ \| __/ _` | __/ _ \/ __|
//        ____) | || (_| | ||  __/\__ \
//       |_____/ \__\__,_|\__\___||___/
//                                    
/////////////////////////////////////////////////////////////////////////////////    
       

        ///////////////////////////////////////////////////
        //// Game & client states
        
        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName, args );

            switch( stateName )
            {
            
            case 'playerTurn':
                this.args = args.args;
                for( var sid in this.args.selectable)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selectable[sid]).addClass("selectable");
                    
                    }
                }

                for( var sid in this.args.selected)
                {
                    if(this.isCurrentPlayerActive())
                    {
                        dojo.query("#"+this.args.selected[sid]).addClass("selected");
                    }
                }

          

                if( this.isCurrentPlayerActive() )
                {
                    if(args.args.titleyou != null) 
                    {
                        $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.titleyou).replace('${you}', this.divYou()).replace(/#opponent#/g,args.args.opponent).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);
                    }
                } 
                    
                else
                {
                    if(args.args.title != null) 
                    {
                        $('pagemaintitletext').innerHTML = this.format_string_recursive(_(args.args.title).replace('${actplayer}', this.divActPlayer()).replace('#nb#',args.args.nb).replace('#nb2#',args.args.nb2).replace('#icon#',args.args.icon).replace('#icon2#',args.args.icon2), args.args);  
                    }
                }

                    
                
                break;
    
           
           
            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            console.log( 'Leaving state: '+stateName );

            dojo.query(".selectable").removeClass("selectable");
            dojo.query(".selected").removeClass("selected");
            
            switch( stateName )
            {
            
                      
           
            case 'dummy':
                break;
            }               
        }, 

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //        
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName, args );
                      
            if( this.isCurrentPlayerActive() )
                {            
                    switch( stateName )
                    {
    
                        case "playerTurn":
                            for( var nb in args.buttons )
                            { 
                                     
                                if(args.buttons[nb] == "cancel")
                                {
                                this.addActionButton( 'cancel', _("Cancel") ,'onOpButton', null, null, 'gray' );
                                }
                                if(args.buttons[nb] == "pass")
                                {
                                this.addActionButton( 'pass', _("Pass") ,'onOpButton', null, null, 'gray' );
                                }
                                if(args.buttons[nb] == "yes") 
                                {
                                this.addActionButton('btn_yes', _("Yes"), 'onOpButton', null, null, 'blue');
                                this.startActionTimer('btn_yes', 5, 1);
                                }
                                if(args.buttons[nb] == "no") 
                                {
                                this.addActionButton( 'no', _("No") ,'onOpButton', null, null, 'red' );
                                }
                            }
                                      
                            
                            break;
    
    
    
                    }
                }
        },        

/////////////////////////////////////////////////////////////////////////////////         
//   _    _ _   _ _ _ _                          _   _               _     
//  | |  | | | (_) (_) |                        | | | |             | |    
//  | |  | | |_ _| |_| |_ _   _   _ __ ___   ___| |_| |__   ___   __| |___ 
//  | |  | | __| | | | __| | | | | '_ ` _ \ / _ \ __| '_ \ / _ \ / _` / __|
//  | |__| | |_| | | | |_| |_| | | | | | | |  __/ |_| | | | (_) | (_| \__ \
//   \____/ \__|_|_|_|\__|\__, | |_| |_| |_|\___|\__|_| |_|\___/ \__,_|___/
//                         __/ |                                           
//                        |___/                                            
/////////////////////////////////////////////////////////////////////////////////  

divYou : function() {
            
    var color = this.players[this.player_id].color;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + _("You") + "</span>";
    return you;
},

divActPlayer : function() {        	
    var color = this.players[this.getActivePlayerId()].color;
    var name = this.players[this.getActivePlayerId()].name;
    var color_bg = "";
    var you = "<span style=\"font-weight:bold;color:#" + color + ";" + color_bg + "\">" + name + "</span>";
    return you;
},

format_string_recursive : function(log, args) {
    try {
        if (log && args && !args.processed) {
            args.processed = true;

            
        }
    } catch (e) {
        console.error(log,args,"Exception thrown", e.stack);
    }
    return this.inherited(arguments);
},
 
attachToNewParentNoDestroy: function (mobile_in, new_parent_in, relation, place_position) 
    {

        const mobile = $(mobile_in);
        const new_parent = $(new_parent_in);

        var src = dojo.position(mobile);
        if (place_position)
            mobile.style.position = place_position;
        dojo.place(mobile, new_parent, relation);
        mobile.offsetTop;//force re-flow
        var tgt = dojo.position(mobile);
        var box = dojo.marginBox(mobile);
        var cbox = dojo.contentBox(mobile);
        var left = box.l + src.x - tgt.x;
        var top = box.t + src.y - tgt.y;

        mobile.style.position = "absolute";
        mobile.style.left = left + "px";
        mobile.style.top = top + "px";
        box.l += box.w - cbox.w;
        box.t += box.h - cbox.h;
        mobile.offsetTop;//force re-flow
        return box;
    },

// TIMER sur bouton

startActionTimer: function(buttonId, time, pref, autoclick = false) {
    var button = $(buttonId);
    var isReadOnly = this.isReadOnly();
    if (button == null || isReadOnly || pref == 2) {
        return;
    }

    // If confirm disabled, click on button
    if (pref == 0) {
        if (autoclick) 
            button.click();
        return;
    }

    this._actionTimerLabel = button.innerHTML;
    this._actionTimerSeconds = time;
    this._actionTimerFunction = () => {
        var button = $(buttonId);
        if (button == null) {
            this.stopActionTimer();
        } 
        else if (this._actionTimerSeconds-- > 1) {
            button.innerHTML = this._actionTimerLabel + ' (' + this._actionTimerSeconds + ')';
        } 
        else {
            //debug('Timer ' + buttonId + ' execute');
            button.click();
            this.stopActionTimer();
        }
    };
    this._actionTimerFunction();
    this._actionTimerId = window.setInterval(this._actionTimerFunction.bind(this), 1000);
    //debug('Timer #' + this._actionTimerId + ' ' + buttonId + ' start');
},

stopActionTimer() {
    if (this._actionTimerId != null) {
        //debug('Timer #' + this._actionTimerId + ' stop');
        window.clearInterval(this._actionTimerId);
        delete this._actionTimerId;
    }
},

isReadOnly: function () {
    return (
        this.isSpectator || typeof g_replayFrom != "undefined" || g_archive_mode
    );
},

//////// RESIZED

onScreenWidthChange: function () {
//this.updateLayout();
},

updateLayout: function () {

    var gameWidth = 2000;
    

    game_play_area = document.getElementById('game_play_area');

    if(game_play_area.offsetWidth <= 2000)
    {
        var horizontalScale = game_play_area.offsetWidth / gameWidth;

        var resized = document.getElementById('resized');
        resized.style.transform = 'scale(' + horizontalScale + ')';

        var scaledHeight = (resized.offsetHeight * horizontalScale);
        game_play_area.style.height = scaledHeight+'px';

    }
},


/////////////////////////////////////////////////////////////////////////////////  
//         _____  _                       _                  _   _             
//        |  __ \| |                     ( )                | | (_)            
//        | |__) | | __ _ _   _  ___ _ __|/ ___    __ _  ___| |_ _  ___  _ __  
//        |  ___/| |/ _` | | | |/ _ \ '__| / __|  / _` |/ __| __| |/ _ \| '_ \ 
//        | |    | | (_| | |_| |  __/ |    \__ \ | (_| | (__| |_| | (_) | | | |
//        |_|    |_|\__,_|\__, |\___|_|    |___/  \__,_|\___|\__|_|\___/|_| |_|
//                         __/ |                                               
//                        |___/                                                
/////////////////////////////////////////////////////////////////////////////////  
        
                
        onSelect: function(evt)
        {        	 
            // Preventing default browser reaction
             dojo.stopEvent( evt );

            
             
            if( !this.isCurrentPlayerActive() || !(evt.currentTarget.classList.contains('selectable')) )
            {   
                return; 
            }
            
            if(this.isCurrentPlayerActive() && evt.currentTarget.classList.contains('selectable'))
            {
                
                this.bgaPerformAction('actSelect', { arg1: evt.currentTarget.id });
            }

        },

        onOpButton: function(evt)
        {
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );
            
            this.bgaPerformAction('actButton', { arg1: evt.currentTarget.id });
            
            

        },

        
///////////////////////////////////////////////////////////////////////////////// 
//       _   _       _   _  __ _           _   _                 
//      | \ | |     | | (_)/ _(_)         | | (_)                
//      |  \| | ___ | |_ _| |_ _  ___ __ _| |_ _  ___  _ __  ___ 
//      | . ` |/ _ \| __| |  _| |/ __/ _` | __| |/ _ \| '_ \/ __|
//      | |\  | (_) | |_| | | | | (_| (_| | |_| | (_) | | | \__ \
//      |_| \_|\___/ \__|_|_| |_|\___\__,_|\__|_|\___/|_| |_|___/
//                                                                 
/////////////////////////////////////////////////////////////////////////////////  

        setupNotifications: function()
        {
            console.log( 'notifications subscriptions setup' );
            
            // TODO: here, associate your game notifications with local methods
            
            // Example 1: standard notification handling
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            
            // Example 2: standard notification handling + tell the user interface to wait
            //            during 3 seconds after calling the method in order to let the players
            //            see what is happening in the game.
            // dojo.subscribe( 'cardPlayed', this, "notif_cardPlayed" );
            // this.notifqueue.setSynchronous( 'cardPlayed', 3000 );
            // 
        },  
        
        // TODO: from this point and below, you can write your game notifications handling methods
        
        /*
        Example:
        
        notif_cardPlayed: function( notif )
        {
            console.log( 'notif_cardPlayed' );
            console.log( notif );
            
            // Note: notif.args contains the arguments specified during you "notifyAllPlayers" / "notifyPlayer" PHP call
            
            // TODO: play the card in the user interface.
        },    
        
        */
   });             
});
