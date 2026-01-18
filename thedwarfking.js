/**
 *------
 * BGA framework: Gregory Isabelli & Emmanuel Colin & BoardGameArena
 * thedwarfking implementation : © <Mathieu Chatrain> <mathieu.chatrain@gmail.com>
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
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {


    /* CONSTANTS HERE */
    const CARD_WIDTH = 150;
    const CARD_HEIGHT = 236;
    const CARDS_PER_ROW = 16;



    return declare("bgagame.thedwarfking", ebg.core.gamegui, {
        constructor: function(){
            console.log('thedwarfking constructor');
              
        
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

            this.my_hand = gamedatas.my_hand;
            this.table = gamedatas.table;
            this.first_player_play = gamedatas.first_player_play;
            this.no_round = parseInt(gamedatas.no_round);
            this.no_trick = parseInt(gamedatas.no_trick);
            this.round_result = gamedatas.round_result;
            this.tricks_max = gamedatas.tricks_max;
            this.view_round = this.no_round;
            this.resume_quest_card = gamedatas.resume_quest_card;
            this.resume_score = gamedatas.resume_score;
            

            this.setupBoard();
            this.setupPannel();
 
            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();

         
            //// CONNECTIONS CLICK
            dojo.query(".stockitem").connect('onclick', this, 'onSelect' )
            dojo.query(".questcardmodal").connect('onclick', this, 'onSelect' )

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

                if (this.args.selectablemulti) {
                    
                    for( var sid in this.args.selectablemulti)
                    {
                        if(this.isCurrentPlayerActive())
                        {
                            dojo.query("#"+this.args.selectablemulti[sid]).addClass("selectablemulti");
                        }
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
            dojo.query(".selectablemulti").removeClass("selectablemulti");
            dojo.query(".selectedmulti").removeClass("selectedmulti");
            
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
                                if(args.buttons[nb] == "continue")
                                {
                                this.addActionButton( 'continue', _("Continue") ,'onOpButton', null, null, 'gray' );
                                }
                                if(args.buttons[nb] == "yes") 
                                {
                                this.addActionButton('yes', _("Yes"), 'onOpButton', null, null, 'blue');
                                //this.startActionTimer('yes', 5, 1);
                                }
                                if(args.buttons[nb] == "no") 
                                {
                                this.addActionButton( 'no', _("No") ,'onOpButton', null, null, 'red' );
                                }
                                if(args.buttons[nb] == "validatemulti")
                                {
                                    this.addActionButton( 'validatemulti', _("Validate selection") ,'onOpValidateMulti', null, null, 'blue' );
                                    dojo.addClass( 'validatemulti', 'disabled');
                                            
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
        
        stopEvent:function (evt) {
            if (evt) {
                evt.preventDefault();
                evt.stopPropagation();
            }
        },
                        
        onSelect: function(evt)
        {        	 
            // Preventing default browser reaction
             dojo.stopEvent( evt );

            if(this.isCurrentPlayerActive())
            {
                if(evt.currentTarget.classList.contains('selectable'))
                {                    
                        this.bgaPerformAction('actSelect', { arg1: evt.currentTarget.id });
                }

                else if(evt.currentTarget.classList.contains('selectablemulti'))
                {
                    const elementId = "#" + evt.currentTarget.id;

                    dojo.query(elementId).removeClass("selectablemulti");
                    setTimeout(function() {
                    dojo.query(elementId).addClass("selectedmulti");
                    }, 10);

                    setTimeout(function() {
                    var elements = document.querySelectorAll('.selectedmulti');
                    var nombreElements = elements.length;
                    var boutonvalidate = document.getElementById('validatemulti');
                    if (boutonvalidate !== null)
                    {
                        if(nombreElements==2)
                        {
                            dojo.removeClass( 'validatemulti', 'disabled');
                        }
                        else
                        {
                            dojo.addClass( 'validatemulti', 'disabled');
                        }
                    }
                    }, 50);

                }

                else if(evt.currentTarget.classList.contains('selectedmulti')) 
                {
                    const elementId = "#" + evt.currentTarget.id;

                    dojo.query(elementId).removeClass("selectedmulti");
                    setTimeout(function() {
                    dojo.query(elementId).addClass("selectablemulti");
                    }, 10);
                
                    setTimeout(function() {
                    var elements = document.querySelectorAll('.selectedmulti');
                    var nombreElements = elements.length;
                    var boutonvalidate = document.getElementById('validatemulti');
                    if (boutonvalidate !== null)
                    {
                        if(nombreElements==2)
                        {
                            dojo.removeClass( 'validatemulti', 'disabled');
                        }
                        else
                        {
                            dojo.addClass( 'validatemulti', 'disabled');
                        }
                    }
                    }, 50);
                   
                }
            }

        },

        onOpButton: function(evt)
        {
            
            // Preventing default browser reaction
            dojo.stopEvent( evt );
            
            this.bgaPerformAction('actButton', { arg1: evt.currentTarget.id });
            
            

        },

        onOpValidateMulti: function(evt) {

            // Preventing default browser reaction
            this.stopEvent( evt );

            

            // Sélectionnez tous les éléments avec la classe spécifiée
            const elementsAvecClasse = document.querySelectorAll(".selectedmulti");

            // Convertissez la NodeList en un tableau et extrayez les IDs
            const ids = Array.from(elementsAvecClasse, element => element.id);

            let result = "";

            ids.forEach(function(id) {
                const parts = id.split("_"); // Split l'ID avec '_'
                result += (result ? "_" : "") + parts[parts.length - 1]; // Ajoute _ sauf pour le premier élément
            
            });

            
            this.bgaPerformAction('actValidateMulti', { arg1: result});
            
        },


        /////////////  BOARD, CARDS AND MODALS ///////////

        /// PANNEL

         setupPannel: function () {

            for (var player in this.players)
            {
                
                    var parent = document.getElementById('player_board_'+player);

                    const container = document.createElement("div");
                    container.id = "infopannel_"+player;
                    container.className = "container_pannel";

                    const icon_trick = document.createElement("div");
                    icon_trick.id = "icon_trick_"+player;
                    icon_trick.className = "icon_trick";

                    const trick_win = document.createElement("div");
                    trick_win.id = "trick_win_"+player;
                    trick_win.className = "trick_win";
                    trick_win.textContent = this.gamedatas.tricks_win[player]; 
                    
                    parent.appendChild(container);

                    var parent_player = document.getElementById("infopannel_"+player);
                    parent_player.appendChild(icon_trick);
                    parent_player.appendChild(trick_win);


                    var info = _('Number of tricks won')
                    var html = '<div>'+info+'</div>';
                    this.addTooltipHtml( "icon_trick_"+player, html, 500);
                    
                    
                

                if(this.getCurrentPlayerId() == player)
                {
                    const last_win_container = document.createElement("div");
                    last_win_container.id = "last_win_container_"+player;
                    last_win_container.className = "last_win_container";
                    parent.appendChild(last_win_container);

                    var info = _('Last trick won')
                    var html = '<div>'+info+'</div>';
                    this.addTooltipHtml( "last_win_container_"+player, html, 500);

                    
                    
                    if(this.gamedatas.tricks_win[player] > 0)
                    {
                        
                        for(index in this.gamedatas.last_trick_win[player])
                        {
                            var type = this.gamedatas.last_trick_win[player][index].type;
                            var type_arg = this.gamedatas.last_trick_win[player][index].type_arg;

                            this.addLastWinTrickCard (player, type, type_arg);
    
                        }
                    }

                }

                const voisins = document.createElement("div");
                voisins.id = "voisins_"+player;
                voisins.className = "voisins_container";
                parent.appendChild(voisins);

                var voisins_container = document.getElementById('voisins_'+player);

                
                const voisin_gauche = document.createElement("div");
                voisin_gauche.id = "voisin_gauche_"+player;
                voisin_gauche.className = "voisin voisin_gauche";
                voisin_gauche.style.color = "#" + this.gamedatas.player_after[player][0].color;

                const fleche_gauche = document.createElement("span");
                fleche_gauche.className = "voisin_fleche";
                fleche_gauche.textContent = "<";

                const nom_gauche = document.createElement("span");
                nom_gauche.className = "voisin_nom";
                nom_gauche.textContent = this.gamedatas.player_after[player][0].name;

                voisin_gauche.appendChild(fleche_gauche);
                voisin_gauche.appendChild(nom_gauche);
                voisins_container.appendChild(voisin_gauche);

                var info1 = _('Player on the left')
                var html1 = '<div>'+info1+'</div>';
                this.addTooltipHtml("voisin_gauche_"+player, html1, 500);

               
                const voisin_droite = document.createElement("div");
                voisin_droite.id = "voisin_droite_"+player;
                voisin_droite.className = "voisin voisin_droite";
                voisin_droite.style.color = "#" + this.gamedatas.player_before[player][0].color;

                const nom_droite = document.createElement("span");
                nom_droite.className = "voisin_nom";
                nom_droite.textContent = this.gamedatas.player_before[player][0].name;

                const fleche_droite = document.createElement("span");
                fleche_droite.className = "voisin_fleche";
                fleche_droite.textContent = ">";

                voisin_droite.appendChild(nom_droite);
                voisin_droite.appendChild(fleche_droite);
                voisins_container.appendChild(voisin_droite);

                var info2 = _('Player on the right')
                var html2 = '<div>'+info2+'</div>';
                this.addTooltipHtml("voisin_droite_"+player, html2, 500);

            }

        },

        // PANNEL LAST TRICK WIN

        addLastWinTrickCard: function(player, card_type, card_type_arg)
        {
                       
            var parent = document.getElementById("last_win_container_"+player);
            var type = card_type;
            var type_arg = card_type_arg;

            if(type == 1)
            {
                var variableY = 0;
            }

            if(type == 2)
            {
                var variableY = -100;
            }

            if(type == 3)
            {
                var variableY = -200;
            }

            if(type == 4)
            {
                var variableY = -300;
            }

            var variableX = (type_arg-1)*(-100);

            const newDiv = document.createElement('div');
            newDiv.classList.add('icon_last_trick_win');
            newDiv.style.backgroundPositionX = variableX + '%';
            newDiv.style.backgroundPositionY = variableY + '%';
            parent.appendChild(newDiv);


            
        },



        /// BOARD

        setupBoard: function () {
        console.log('Setting up the board');

        const textRound = this.no_round > 7
        ? `${_("Tiebreaker Round")}`
        : `${_("Round")} : ${this.no_round} / 7`;

        
        const gameBoardHTML = `
            
                    <div id="round">
                    <div id="nb_round">${textRound}</div>
                    <div id="btn_round"><div class="icon1_btn"></div><div>/</div><div class="icon2_btn"></div></div>
                    </div>

                    <div id="btn_resume_round">
                    </div>

                    <div id="modal_resume_round_container" class="hidden">
                        <div id="modal_resume_round">
                            <div id="croix2">X</div>
                            <div id="prev_round"><<</div>
                            <div id="next_round">>></div>
                            <div id="resume_round"></div>
                        </div>
                    </div>

                    <div id="table_cards_container" class="cards-container">
                        <div class="titre">${_("Cards played")}</div>
                        <div id="table_cards" class="cards"></div>
                        
                    </div> 

                    
                    <div id="hand_container" class="cards-container">
                        <div class="titre" id="my_cards_title">${_("My hand")}</div>
                        <div id="my_cards" class="cards"></div>
                    </div>

                    <div id="modal_round_container">
                        <div id="modal_round">
                        <div id="modal_section_1" class="modal_section"></div>
                        <div id="modal_section_2" class="modal_section"></div>
                        <div id="croix">X</div>
                        </div>
                    </div>

                    <div id="modal_players_container" class="hidden">
                        <div id="modal_players">
                        </div>
                    </div>

                    <div id="modal_fleches_container" class="hidden">
                        <div id="modal_fleches">
                        </div>
                    </div>

                    <div id="modal_previoustrick_container" class="hidden">
                        <div id="modal_previoustrick">
                        </div>
                    </div>
                        
        `
        
        
        const gamePlayArea = document.getElementById("board_id");
        gamePlayArea.insertAdjacentHTML("beforeend", gameBoardHTML);

        var info = _('Special Card and Quest for the Round');
        var html = '<div>'+info+'</div>';
        this.addTooltipHtml( "btn_round", html, 500);

        var info_resume = _('Round summary');
        var html_resume = '<div>'+info_resume+'</div>';
        this.addTooltipHtml( "btn_resume_round", html_resume, 500);


        var choiceQuest = this.gamedatas.active_quest_card;
        if(choiceQuest[0].validate == 0)
        {
            var modal_round_container = document.getElementById("modal_round_container");
            modal_round_container.classList.remove("hidden");
            
        }
        else
        {
            var modal_round_container = document.getElementById("modal_round_container");
            modal_round_container.classList.add("hidden");
            
        }

        this.addEventListenerModal();
        this.addEventListenerModalResumeRound();
        this.addEventListenerResumeScore();
       


        var druide = this.gamedatas.druide_player;
        if((this.getCurrentPlayerId() == druide)&&(!this.isSpectator ))
        {
            this.addPlayersModal(this.players, druide);
        }

        var pe_rouge = this.gamedatas.pe_rouge_player;
        if((this.getCurrentPlayerId() == pe_rouge)&&(!this.isSpectator ))
        {
            this.addPlayersModal(this.players, pe_rouge);
        }

        var pe_blue = this.gamedatas.pe_blue_player;
        if((this.getCurrentPlayerId() == pe_blue)&&(!this.isSpectator ))
        {
            this.addFlecheModal();
        }

        var sorcier = this.gamedatas.sorcier_player;
        if((this.getCurrentPlayerId() == sorcier)&&(!this.isSpectator ))
        {
            this.addPreviousTrickModal(this.gamedatas.previous_trick_cards);
        }
        

        if(this.isSpectator ) {
        
        dojo.addClass('hand_container', 'hidden');
        
        let board = document.getElementById("board_id");
        board.style.height = "700px";
        }

        this.setupSpecialModal(this.gamedatas.active_special_card);
        this.setupQuestModal(this.gamedatas.active_quest_card);
        this.setupStocks();
        this.addTooltipsInit();

        },

        /// PLAYERS MODAL

        addPlayersModal: function(players, player_play)
        {
                        
            Object.values(players).forEach((player) => {
                if(player.id != player_play)
                {

                var parent = document.getElementById("modal_players");

                const modal_player = document.createElement("div");
                modal_player.id = 'modal_player_'+player.id; 
                modal_player.classList.add('modal_player');

                var avatar ='avatar_'+player.id;
                var avatarImage = document.getElementById(avatar);
                var avatarSrc = avatarImage.src;
                var newImage = document.createElement('img');
                newImage.src = avatarSrc;
                newImage.id = 'newavatar_'+player.id; 
                newImage.classList.add('newAvatar');
                modal_player.appendChild(newImage);


                const player_name = document.createElement("div");
                player_name.id = "modal_player_name_"+player.id;
                player_name.style.color = "#"+player.color;
                player_name.classList.add('modal_player_name');
                player_name.textContent = player.name;
                modal_player.appendChild(player_name);

                parent.appendChild(modal_player);

                }
            });

            var modal_resume_round_container = document.getElementById("modal_resume_round_container");
            if (!modal_resume_round_container.classList.contains("hidden")) {
                modal_resume_round_container.classList.add("hidden");
            }
            var modal_round_container = document.getElementById("modal_round_container");
            if (!modal_round_container.classList.contains("hidden")) {
                modal_round_container.classList.add("hidden");
            }

            var modal_players_container = document.getElementById("modal_players_container");
            modal_players_container.classList.remove("hidden");
            var round = document.getElementById("round");
            round.classList.add("index");
            var btn_resume_round = document.getElementById("btn_resume_round");
            btn_resume_round.classList.add("index");


            dojo.query(".modal_player").connect('onclick', this, 'onSelect' )
            
        },

        removePlayersModal: function()
        {
            document.querySelectorAll('.modal_player').forEach(el => el.remove());
            var parent = document.getElementById("modal_players_container");
            parent.classList.add("hidden");
            
            var round = document.getElementById("round");
            round.classList.remove("index");
            var btn_resume_round = document.getElementById("btn_resume_round");
            btn_resume_round.classList.remove("index");
            
            
        },

        /// FLECHES MODAL

        addFlecheModal: function()
        {
            var parent = document.getElementById("modal_fleches");

            const container_fleche1 = document.createElement("div");
            container_fleche1.id = 'container_fleche_1'; 
            container_fleche1.classList.add('container_fleche');
            parent.appendChild(container_fleche1);

            const container_fleche2 = document.createElement("div");
            container_fleche2.id = 'container_fleche_2'; 
            container_fleche2.classList.add('container_fleche');
            parent.appendChild(container_fleche2);

            
            const fleche1 = document.createElement("div");
            fleche1.id = 'fleche1'; 
            fleche1.classList.add('fleche1');
            container_fleche1.appendChild(fleche1);

            const fleche2 = document.createElement("div");
            fleche2.id = 'fleche2'; 
            fleche2.classList.add('fleche2');
            container_fleche2.appendChild(fleche2);

            var text1 = _('Next Player');
            var text2 = _('Previous Player');

            const texte_fleche1 = document.createElement("div");
            texte_fleche1.id = 'text_fleche1'; 
            texte_fleche1.classList.add('texte_fleche');
            texte_fleche1.innerHTML = text1;
            container_fleche1.appendChild(texte_fleche1);

            const texte_fleche2 = document.createElement("div");
            texte_fleche2.id = 'text_fleche2'; 
            texte_fleche2.classList.add('texte_fleche');
            texte_fleche2.innerHTML = text2;
            container_fleche2.appendChild(texte_fleche2);

            var modal_resume_round_container = document.getElementById("modal_resume_round_container");
            if (!modal_resume_round_container.classList.contains("hidden")) {
                modal_resume_round_container.classList.add("hidden");
            }
            var modal_round_container = document.getElementById("modal_round_container");
            if (!modal_round_container.classList.contains("hidden")) {
                modal_round_container.classList.add("hidden");
            }
                    
           
            var modal_fleches_container = document.getElementById("modal_fleches_container");
            modal_fleches_container.classList.remove("hidden");
            var round = document.getElementById("round");
            round.classList.add("index");
            var btn_resume_round = document.getElementById("btn_resume_round");
            btn_resume_round.classList.add("index");


            dojo.query(".container_fleche").connect('onclick', this, 'onSelect' )
            
        },

        removeFlecheModal: function()
        {
            document.querySelectorAll('.container_fleche').forEach(el => el.remove());
            var parent = document.getElementById("modal_fleches_container");
            parent.classList.add("hidden");
            
            var round = document.getElementById("round");
            round.classList.remove("index");
            var btn_resume_round = document.getElementById("btn_resume_round");
            btn_resume_round.classList.remove("index");
            
            
        },


        /// PREVIOUS TRICK MODAL

        addPreviousTrickModal: function(cards)
        {
            var parent = document.getElementById("modal_previoustrick");
                        
            Object.values(cards).forEach((card) => {
                
                var type = card.type;
                var type_arg = card.type_arg;
                
                var variableY = (type-1)*(-100);

                if(type_arg <= 10)
                {
                    var variableX = (type_arg-1)*(-100);
                }
                if(type_arg >= 12)
                {
                    var variableX = (type_arg)*(-100);
                }

                var previous_card = document.createElement("div");
                previous_card.id = 'previous_card_'+card.id; 
                previous_card.classList.add('previous_card');
                previous_card.style.backgroundPositionX = variableX + '%';
                previous_card.style.backgroundPositionY = variableY + '%';
                parent.appendChild(previous_card);


                this.addTooltipsCard('previous_card_'+card.id, type, type_arg, 0);

                
            });

            var modal_resume_round_container = document.getElementById("modal_resume_round_container");
            if (!modal_resume_round_container.classList.contains("hidden")) {
                modal_resume_round_container.classList.add("hidden");
            }
            var modal_round_container = document.getElementById("modal_round_container");
            if (!modal_round_container.classList.contains("hidden")) {
                modal_round_container.classList.add("hidden");
            }



            var modal_previoustrick_container = document.getElementById("modal_previoustrick_container");
            modal_previoustrick_container.classList.remove("hidden");
            var round = document.getElementById("round");
            round.classList.add("index");
            var btn_resume_round = document.getElementById("btn_resume_round");
            btn_resume_round.classList.add("index");

            dojo.query(".previous_card").connect('onclick', this, 'onSelect' )
            
        },

        removePreviousTrick: function()
        {
            document.querySelectorAll('.previous_card').forEach(el => el.remove());
            var parent = document.getElementById("modal_previoustrick_container");
            parent.classList.add("hidden");
            
            var round = document.getElementById("round");
            round.classList.remove("index");
            var btn_resume_round = document.getElementById("btn_resume_round");
            btn_resume_round.classList.remove("index");
            
            
        },


        
        // CARDS AND STOCKS AND INFOS MOMIE AND INFOS CLONE
     
        createStockForCards: function(page, element)
        {
            let stock = new ebg.stock();
            stock.create(page, element, CARD_WIDTH, CARD_HEIGHT);
            stock.image_items_per_row = CARDS_PER_ROW;

            return stock;
        },

        getStockCardType: function( card ) {
        let card_id;
        if (card.type == 1) {

            if(card.type_arg < 11)
            {
                card_id = parseInt(card.type_arg);
            }

            else if (card.type_arg == 11) {

                if(card.type_arg_2 == 1)
                {
                    card_id = 11;
                }

                else if(card.type_arg_2 == 2)
                {
                    card_id = 12;
                }
            }

            else if (card.type_arg > 11) {

                card_id = 1 + parseInt(card.type_arg);
            }
            
        } 
        
        else if (card.type == 2) {

            if(card.type_arg < 11)
            {
                card_id = 16 + parseInt(card.type_arg);
            }

            else if (card.type_arg == 11) {

                if(card.type_arg_2 == 1)
                {
                    card_id = 27;
                }

                else if(card.type_arg_2 == 2)
                {
                    card_id = 28;
                }
            }

            else if (card.type_arg > 11) {

                card_id = 17 + parseInt(card.type_arg);
            }
        } 

        else if (card.type == 3) {

            if(card.type_arg < 11)
            {
                card_id = 32 + parseInt(card.type_arg);
            }

            else if (card.type_arg == 11) {

                if(card.type_arg_2 == 1)
                {
                    card_id = 43;
                }

                else if(card.type_arg_2 == 2)
                {
                    card_id = 44;
                }
            }

            else if (card.type_arg > 11) {

                card_id = 33 + parseInt(card.type_arg);
            }
        }
        
        else if (card.type == 4) {
            
                card_id = 48 + parseInt(card.type_arg);
        } 


        return card_id

        },



    setupStocks: function() {

    // Stock pour la main du joueur
    this.handStock = this.createStockForCards(this, $('my_cards'));
    this.handStock.setSelectionMode(0);
    this.handStock.setOverlap(60, 0);
    this.handStock.use_vertical_overlap_as_offset = false;
    this.handStock.vertical_overlap = -5;
    for( var card_id = 1; card_id <= 53; card_id++) {
        this.handStock.addItemType(card_id, card_id, g_gamethemeurl + 'img/cards.jpg', card_id-1);
    }


    // Stock pour la table : pas de weight pour la table pour ne pas classer les cartes selon leur type.
    this.tableStock = this.createStockForCards(this, $('table_cards'));
    for( var card_id = 1; card_id <= 53; card_id++) {
        this.tableStock.addItemType(card_id, 0, g_gamethemeurl + 'img/cards.jpg', card_id-1);
    }
    this.tableStock.setSelectionMode(0);
    this.tableStock.use_vertical_overlap_as_offset = false;
    this.tableStock.vertical_overlap = -15;


    
    // Cards in player's hand
    Object.values(this.my_hand).forEach( card =>
    {
        const card_type = this.getStockCardType(card);
        this.handStock.addToStockWithId(card_type, card.id);
    } );
    this.handStock.updateDisplay();


    //Cards in table
    Object.values(this.table).forEach((card) => {

        
        const card_type = this.getStockCardType(card);
        this.tableStock.addToStockWithId(card_type, card.id);

        const player = this.players[card.location_arg];

        const card_div = document.getElementById('table_cards_item_' + card.id);
        dojo.place('<div class="player-title" style="color: #' + player.color + '">' + player.name + '</div>', card_div);
    });


    //momie
    if(this.gamedatas.momie_id_table != null)
    {
        this.addMomieTable(this.gamedatas.momie_id_table, this.gamedatas.type_last_card_win, this.gamedatas.typearg_last_card_win);
    }
    else
    {
        this.addMomie(this.gamedatas.no_trick, this.gamedatas.momie_id, this.gamedatas.momie_player, this.gamedatas.type_last_card_win, this.gamedatas.typearg_last_card_win);
    }

    //clone
    if(this.gamedatas.clone_id_table != null)
    {
        this.addCloneTable(this.gamedatas.clone_id_table, this.gamedatas.type_last_card, this.gamedatas.typearg_last_card);
    }
    else if(this.gamedatas.type_last_card != null)
    {
        this.addClone(this.gamedatas.clone_id, this.gamedatas.clone_player, this.gamedatas.type_last_card, this.gamedatas.typearg_last_card);
    }
    
    

    },


    addMomie: function(trick, momie_id, momie_player, type_last_card_win, typearg_last_card_win)
    {
        var momie_last = document.getElementById('last_card_momie');
        if(momie_last)
        {
            momie_last.remove();
        }
        
        if(trick >= 2 && momie_id != null && momie_player == this.getCurrentPlayerId())
        {
        
            var momie = document.getElementById('my_cards_item_' + momie_id);
            var type = type_last_card_win;
            var type_arg = typearg_last_card_win;

            if(type == 1)
            {
                var variableY = 0;
            }

            if(type == 2)
            {
                var variableY = -100;
            }

            if(type == 3)
            {
                var variableY = -200;
            }

            if(type == 4)
            {
                var variableY = -300;
            }

            var variableX = (type_arg-1)*(-100);

            const newDiv = document.createElement('div');
            newDiv.id = 'last_card_momie';
            newDiv.classList.add('last_card_momie');
            newDiv.style.backgroundPositionX = variableX + '%';
            newDiv.style.backgroundPositionY = variableY + '%';
            momie.appendChild(newDiv);


        }
        
    },


    addMomieTable: function(momie_id, type_last_card_win, typearg_last_card_win)
    {
        var momie_last = document.getElementById('last_card_momie');
        if(momie_last)
        {
            momie_last.remove();
        }
        
            
            var momie = document.getElementById('table_cards_item_' + momie_id);
            var type = type_last_card_win;
            var type_arg = typearg_last_card_win;

            if(type == 1)
            {
                var variableY = 0;
            }

            if(type == 2)
            {
                var variableY = -100;
            }

            if(type == 3)
            {
                var variableY = -200;
            }

            if(type == 4)
            {
                var variableY = -300;
            }

            var variableX = (type_arg-1)*(-100);

            const newDiv = document.createElement('div');
            newDiv.id = 'last_card_momie';
            newDiv.classList.add('last_card_momie');
            newDiv.style.backgroundPositionX = variableX + '%';
            newDiv.style.backgroundPositionY = variableY + '%';
            momie.appendChild(newDiv);


        
        
    },


    addClone: function(clone_id, clone_player, type_last_card, typearg_last_card)
    {
        var clone_last = document.getElementById('last_card_clone');
        if(clone_last)
        {
            clone_last.remove();
        }
        
        if(clone_id != null && clone_player == this.getCurrentPlayerId())
        {
        
            var clone = document.getElementById('my_cards_item_' + clone_id);
            var type = type_last_card;
            var type_arg = typearg_last_card;

            if(type == 1)
            {
                var variableY = 0;
            }

            if(type == 2)
            {
                var variableY = -100;
            }

            if(type == 3)
            {
                var variableY = -200;
            }

            if(type == 4)
            {
                var variableY = -300;
            }

            var variableX = (type_arg-1)*(-100);

            const newDiv = document.createElement('div');
            newDiv.id = 'last_card_clone';
            newDiv.classList.add('last_card_clone');
            newDiv.style.backgroundPositionX = variableX + '%';
            newDiv.style.backgroundPositionY = variableY + '%';
            clone.appendChild(newDiv);


        }
        
    },

    addCloneTable: function(clone_id, type_last_card, typearg_last_card)
    {
        var clone_last = document.getElementById('last_card_clone');
        if(clone_last)
        {
            clone_last.remove();
        }
        
            
            var clone = document.getElementById('table_cards_item_' + clone_id);
            var type = type_last_card;
            var type_arg = typearg_last_card;

            if(type == 1)
            {
                var variableY = 0;
            }

            if(type == 2)
            {
                var variableY = -100;
            }

            if(type == 3)
            {
                var variableY = -200;
            }

            if(type == 4)
            {
                var variableY = -300;
            }

            var variableX = (type_arg-1)*(-100);

            const newDiv = document.createElement('div');
            newDiv.id = 'last_card_clone';
            newDiv.classList.add('last_card_clone');
            newDiv.style.backgroundPositionX = variableX + '%';
            newDiv.style.backgroundPositionY = variableY + '%';
            clone.appendChild(newDiv);


        
        
    },

    /// SPECIAL CARD + QUEST MODAL
        
        addEventListenerModal: function() {
            var modal_round_container = document.getElementById("modal_round_container");
            var croix = document.getElementById("croix");
            var btn = document.getElementById("btn_round");

            // On stocke les handlers dans this pour pouvoir les enlever après
            this._croixHandler = () => {
                modal_round_container.classList.add("hidden");
            };

            this._btnHandler = () => {
                var modal_resume_round_container = document.getElementById("modal_resume_round_container");
                if (!modal_resume_round_container.classList.contains("hidden")) {
                    modal_resume_round_container.classList.add("hidden");
                }
                modal_round_container.classList.toggle("hidden");
            };

            croix.addEventListener("click", this._croixHandler);
            btn.addEventListener("click", this._btnHandler);
        },

        addEventListenerModalResumeRound: function() {

            var modal_resume_round_container = document.getElementById("modal_resume_round_container");
            var croix2 = document.getElementById("croix2");
            var btn2 = document.getElementById("btn_resume_round");

            // On stocke les handlers dans this pour pouvoir les enlever après
            this._croixHandler2 = () => {
                modal_resume_round_container.classList.add("hidden");
            };

            this._btnHandler2 = () => {
                var modal_round_container = document.getElementById("modal_round_container");
                if (!modal_round_container.classList.contains("hidden")) {
                    modal_round_container.classList.add("hidden");
                }
                modal_resume_round_container.classList.toggle("hidden");
                this.view_round = this.no_round;
                this.detailScore(this.no_round);
            };

            croix2.addEventListener("click", this._croixHandler2);
            btn2.addEventListener("click", this._btnHandler2);

            
            
        },

        addEventListenerResumeScore: function() {
            
            this._showprev = () => {
                this.view_round--;
                this.detailScore(this.view_round);
            };

            this._shownext = () => {
                this.view_round++;
                this.detailScore(this.view_round);
            };

            var prev = document.getElementById("prev_round");
            var next = document.getElementById("next_round");

            prev.addEventListener("click", this._showprev);
            next.addEventListener("click", this._shownext);


        },

        setupSpecialModal: function(data)
        {
            /* SPECIAL CARD*/
            const parent = document.getElementById("modal_section_1");

            var type = data[0].type;
            var type_arg = data[0].type_arg;
            var type_arg_2 = data[0].type_arg_2;

            if(type == 4)
            {
                var variableX = (type_arg - 1)*(-100);
                var variableY = 0;
            }

            if(type == 1)
            {
                if(type_arg == 1)
                {
                    var variableX = -500;
                    var variableY = 0;

                }
                if((type_arg == 11)&&(type_arg_2 == 1))
                {
                    var variableX = -600;
                    var variableY = 0;
                    
                }
                if((type_arg == 11)&&(type_arg_2 == 2))
                {
                    var variableX = 0;
                    var variableY = -100;
                    
                }       
                
            }

            if(type == 2)
            {
                if(type_arg == 1)
                {
                    var variableX = -100;
                    var variableY = -100;

                }
                if((type_arg == 11)&&(type_arg_2 == 1))
                {
                    var variableX = -200;
                    var variableY = -100;
                    
                }
                if((type_arg == 11)&&(type_arg_2 == 2))
                {
                    var variableX = -300;
                    var variableY = -100;
                    
                } 
            
            }

            if(type == 3)
            {
                if(type_arg == 1)
                {
                    var variableX = -400;
                    var variableY = -100;

                }
                if((type_arg == 11)&&(type_arg_2 == 1))
                {
                    var variableX = -500;
                    var variableY = -100;
                    
                }
                if((type_arg == 11)&&(type_arg_2 == 2))
                {
                    var variableX = -600;
                    var variableY = -100;
                    
                } 
                
            }

            const enfantspecialcard = document.createElement("div");
            enfantspecialcard.id = "specialcardmodal";
            enfantspecialcard.className = "specialcardmodal";
            enfantspecialcard.style.backgroundPositionX = variableX + "%";
            enfantspecialcard.style.backgroundPositionY = variableY + "%";

            parent.appendChild(enfantspecialcard);

            this.addTooltipsCard(specialcardmodal, type, type_arg, type_arg_2);


        },


        setupQuestModal: function(data)
        {
            /* QUEST CARD */

            var type = data[0].rand;
            var validate = data[0].validate;

            if(type >=1 && type <=10)
            {

                var variableX = -100*(Number(type) - 1);
                var variableY = 0;

            }

            if(type >=11 && type <=20)
            {

                var variableX = -100*(Number(type) - 11);
                var variableY = -200;

            }

            const parent2 = document.getElementById("modal_section_2");

            if(validate == 0 || validate == 1)
            {
                const enfantquestcard1 = document.createElement("div");
                enfantquestcard1.id = "questcardmodal1";
                enfantquestcard1.className = "questcardmodal";
                enfantquestcard1.style.backgroundPositionX = variableX + "%";
                enfantquestcard1.style.backgroundPositionY = variableY + "%";
                parent2.appendChild(enfantquestcard1);
                this.addTooltipsQuest(1, type+'1');

            }

            if(validate == 0 || validate == 2)
            {
                const enfantquestcard2 = document.createElement("div");
                enfantquestcard2.id = "questcardmodal2";
                enfantquestcard2.className = "questcardmodal";
                enfantquestcard2.style.backgroundPositionX = variableX + "%";
                enfantquestcard2.style.backgroundPositionY = (variableY-100) + "%";
                parent2.appendChild(enfantquestcard2);
                this.addTooltipsQuest(2, type+'2');
            }
        },


        // TOOLTIPS

        addTooltipsInit: function()
        {            
            Object.values(this.gamedatas.all_cards).forEach((card) => {
                
                let element = card.type+card.type_arg+card.type_arg_2;
               
                if(this.gamedatas.tooltips_cards[element])
                {
                    const name = _(this.gamedatas.tooltips_cards[element].name);
                    const text = _(this.gamedatas.tooltips_cards[element].text);
                    const html = `<div class="tooltips-container">
                    <div class="tooltips">
                    <div class="tooltips-title">- ${name} -</div>
                    <div class="tooltips-text">${text}</div>
                    </div>
                    </div>`;
                   
                    if (card.location == 'table')
                    {
                        this.addTooltipHtml('table_cards_item_'+card.id, html, 500);
                    }

                    if(card.location == 'hand')
                    {
                        this.addTooltipHtml('my_cards_item_'+card.id, html, 500);
                    }
                
                }
       
            });

        },

        addTooltipsCard: function(id, type, type_arg, type_arg_2)
        {            
            
                
            let element = type+type_arg+type_arg_2;
            
            if(this.gamedatas.tooltips_cards[element])
            {
                const name = _(this.gamedatas.tooltips_cards[element].name);
                const text = _(this.gamedatas.tooltips_cards[element].text);
                const html = `<div class="tooltips-container">
                <div class="tooltips">
                <div class="tooltips-title">- ${name} -</div>
                <div class="tooltips-text">${text}</div>
                </div>
                </div>`;
                
                
                this.addTooltipHtml(id, html, 500);
                
            
            }
       
            

        },

        addTooltipsQuest: function(id, quest)
        {            
                          
                        
            if(this.gamedatas.tooltips_quests[quest])
            {
                const name = _(this.gamedatas.tooltips_quests[quest].name);
                const text = _(this.gamedatas.tooltips_quests[quest].text);
                let text2 = '';
                if(this.gamedatas.tooltips_quests[quest].text2)
                {
                    text2 = _(this.gamedatas.tooltips_quests[quest].text2);
                }
                const html = `<div class="tooltips-container">
                <div class="tooltips">
                <div class="tooltips-title">- ${name} -</div>
                <div class="tooltips-text">${text}</div>
                <div class="tooltips-text">${text2}</div>
                </div>
                </div>`;
                
                
                this.addTooltipHtml('questcardmodal'+id, html, 500);
                
            
            }
       
            

        },

        addTooltipsQuestResume: function(quest)
        {            
                          
                        
            if(this.gamedatas.tooltips_quests[quest])
            {
                const name = _(this.gamedatas.tooltips_quests[quest].name);
                const text = _(this.gamedatas.tooltips_quests[quest].text);
                let text2 = '';
                if(this.gamedatas.tooltips_quests[quest].text2)
                {
                    text2 = _(this.gamedatas.tooltips_quests[quest].text2);
                }
                const html = `<div class="tooltips-container">
                <div class="tooltips">
                <div class="tooltips-title">- ${name} -</div>
                <div class="tooltips-text">${text}</div>
                <div class="tooltips-text">${text2}</div>
                </div>
                </div>`;
                
                
                this.addTooltipHtml('questresumescore', html, 500);
                
            
            }
       
            

        },


        // Details Scores

        detailScore: function(no_round)
        {

                       
            var container = document.getElementById("resume_round");
            container.textContent = '';

            var prev = document.getElementById("prev_round");
            var next = document.getElementById("next_round");

            if(this.no_round == 1)
            {
                prev.classList.add("hidden");
                next.classList.add("hidden");
            }

            else
            {
                if(no_round < this.no_round && no_round > 1)
                {
                    prev.classList.remove("hidden");
                    next.classList.remove("hidden");
                }

                if(no_round < this.no_round && no_round == 1)
                {
                    prev.classList.add("hidden");
                    next.classList.remove("hidden");
                }

                if(no_round == this.no_round)
                {
                    prev.classList.remove("hidden");
                    next.classList.add("hidden");
                }
            }

            
            const text_round = _('Round');
            const text_trick = _('Trick');
            const text_trick_in_progress = _('Trick in progress ...');

            const round = document.createElement("div");
            round.classList.add('round_title');
            round.textContent = text_round + ' ' + no_round;
            container.appendChild(round);
            
           Object.entries(this.round_result[no_round])
            .forEach(([trick, detail]) => {

                const ligne_trick = document.createElement("div");
                ligne_trick.id = 'trick_'+no_round+'_'+trick;
                ligne_trick.classList.add('ligne_trick');
                container.appendChild(ligne_trick);

                const ligne_trick_detail = document.getElementById('trick_'+no_round+'_'+trick);

                const trick_title = document.createElement("div");
                trick_title.classList.add('trick_title');
                trick_title.textContent = text_trick + ' ' + trick + ':';
                ligne_trick_detail.appendChild(trick_title);


                const played_cards = document.createElement("div");
                played_cards.id = 'played_card_'+no_round+'_'+trick;
                played_cards.classList.add('played_cards_list');
                ligne_trick_detail.appendChild(played_cards);

                const player_id_win = this.round_result[no_round][trick]['winner'];

                if(player_id_win)
                {
                    const player_name_win = this.players[player_id_win].name;
                    const player_color_win = this.players[player_id_win].color;

                    var avatar ='avatar_'+player_id_win;
                    var avatarImage = document.getElementById(avatar);
                    var avatarSrc = avatarImage.src;
                    var newImage = document.createElement('img');
                    newImage.src = avatarSrc;
                    newImage.id = 'win_avatar_'+player_id_win+'_'+no_round+'_'+trick; 
                    newImage.classList.add('resume_score_avatar');
                    ligne_trick_detail.appendChild(newImage);

                    const html = `<div style="color: #${player_color_win}">${player_name_win}</div>`;
                                
                    this.addTooltipHtml('win_avatar_'+player_id_win+'_'+no_round+'_'+trick, html, 500);
                    
                }

                const detail_played_cards = document.getElementById('played_card_'+no_round+'_'+trick);
                
                if(detail.cards_played.length != 0)
                {

                    for(let i = 0; i < detail.cards_played.length; i++)
                    {                                                                       
                            var type = detail.cards_played[i].type;
                            var type_arg = detail.cards_played[i].type_arg;

                            if(type == 1)
                            {
                                var variableY = 0;
                            }

                            if(type == 2)
                            {
                                var variableY = -100;
                            }

                            if(type == 3)
                            {
                                var variableY = -200;
                            }

                            if(type == 4)
                            {
                                var variableY = -300;
                            }

                            var variableX = (type_arg-1)*(-100);

                            const newDiv = document.createElement('div');
                            newDiv.classList.add('icon_last_trick_win');
                            newDiv.style.backgroundPositionX = variableX + '%';
                            newDiv.style.backgroundPositionY = variableY + '%';
                            detail_played_cards.appendChild(newDiv);
                        
                    }
                }

                else
                {
                    
                    const TickinProgres = document.createElement('div');
                    TickinProgres.textContent = text_trick_in_progress;
                    detail_played_cards.appendChild(TickinProgres);
                }

                                
            });

            /* QUEST CARD */

            if(this.resume_quest_card[no_round])
            {
                
                var type = this.resume_quest_card[no_round][0].rand;
                var validate = this.resume_quest_card[no_round][0].validate;

                if(type >=1 && type <=10)
                {

                    var variableX = -100*(Number(type) - 1);
                    var variableY = 0;

                }

                if(type >=11 && type <=20)
                {

                    var variableX = -100*(Number(type) - 11);
                    var variableY = -200;

                }

                if(validate != 0)
                {
                    const quest = document.createElement("div");
                    quest.id = "questresumescore";
                    quest.className = "questresumescore";

                    if(validate == 1)
                    {
                        quest.style.backgroundPositionX = variableX + "%";
                        quest.style.backgroundPositionY = variableY + "%";
                        container.appendChild(quest);
                        this.addTooltipsQuestResume(type+'1');

                    }

                    if(validate == 2)
                    {
                        quest.style.backgroundPositionX = variableX + "%";
                        quest.style.backgroundPositionY = (variableY-100) + "%";
                        container.appendChild(quest);
                        this.addTooltipsQuestResume(type+'2');
                    }

                    
                }

            }

            /* SCORE */

            //if(no_round != this.no_round)
            if(this.resume_score[no_round])
            {
                const text_quest = _('Quest');
                const text_bonus = _('Bonus');

                const text_score = _('Scores');
                const score = document.createElement("div");
                score.classList.add('round_title_score');
                score.textContent = text_score+":";
                container.appendChild(score);


                var ligne_score = document.createElement("div");
                ligne_score.id = "ligne_score_"+no_round;
                ligne_score.classList.add('ligne_score');
                container.appendChild(ligne_score);

                var ligne = document.getElementById("ligne_score_"+no_round);


                Object.entries(this.resume_score[no_round])
                .forEach(([key, detail]) => {

                    var player_name = this.players[detail.player_id].name;
                    var player_color = this.players[detail.player_id].color;

                    const div = document.createElement("div");
                    div.id = 'player_score_info_'+detail.player_id+'_'+no_round;
                    div.classList.add('player_score_info');
                    ligne.appendChild(div);

                    var div_player = document.getElementById('player_score_info_'+detail.player_id+'_'+no_round);

                    var avatar2 ='avatar_'+detail.player_id;
                    var avatarImage2 = document.getElementById(avatar2);
                    var avatarSrc2 = avatarImage2.src;
                    var newImage2 = document.createElement('img');
                    newImage2.src = avatarSrc2;
                    newImage2.id = 'score_avatar_'+detail.player_id+'_'+no_round; 
                    newImage2.classList.add('resume_score_avatar');
                    div_player.appendChild(newImage2);

                    const html = `<div style="color: #${player_color}">${player_name}</div>`;
                    this.addTooltipHtml('score_avatar_'+detail.player_id+'_'+no_round, html, 500);

                    const score = document.createElement("div");
                    score.id = "score_"+detail.player_id+"_"+no_round;
                    score.classList.add('detail_score');
                    score.innerHTML = `<span>${detail.score_round}</span> <span class="log_pv title"></span>`
                    div_player.appendChild(score);

                    if(detail.score_bonus != 0)
                    {
                        var html2 = `<span>${text_quest}:</span> <span>&nbsp;${detail.score_quest}</span> <span class="log_pv title"></span> <span>&nbsp;+ ${text_bonus}: </span> <span>${detail.score_bonus}</span> <span class="log_pv title"></span>`;
                    }
                    else if(detail.bonus_enchanteur == 2)
                    {
                        var html2 = `<span>${text_quest}:</span> <span>&nbsp;${detail.score_quest}</span> <span class="log_pv title"></span> <span>&nbsp;x2</span>`;
                    }
                    else
                    {
                        var html2 = `<span>${text_quest}:</span> <span>&nbsp;${detail.score_quest}</span> <span class="log_pv title"></span>`;
                    }
                    
                    this.addTooltipHtml("score_"+detail.player_id+"_"+no_round, html2, 500);

                   
                    
                });

                
            }

            
            
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
            
            dojo.subscribe( 'playCard', this, "notif_playCard" );
            dojo.subscribe( 'endTurn', this, "notif_endTurn" );
            dojo.subscribe( 'ChoiceQuest', this, "notif_ChoiceQuest" );
            dojo.subscribe( 'majRound', this, "notif_majRound" );
            dojo.subscribe( 'drawCards', this, "notif_drawCards" );
            dojo.subscribe( 'majModal', this, "notif_majModal" );
            dojo.subscribe( 'momieChange', this, "notif_momieChange" );
            dojo.subscribe( 'removeCards', this, "notif_removeCards" );
            dojo.subscribe( 'showPlayersModal', this, "notif_showPlayersModal" );
            dojo.subscribe( 'removePlayersModal', this, "notif_removePlayersModal" );
            dojo.subscribe( 'showFlecheModal', this, "notif_showFlecheModal" );
            dojo.subscribe( 'removeFlecheModal', this, "notif_removeFlecheModal" );
            dojo.subscribe( 'showPreviousTrick', this, "notif_showPreviousTrick" );
            dojo.subscribe( 'removePreviousTrick', this, "notif_removePreviousTrick" );
            dojo.subscribe( 'cloneChange', this, "notif_cloneChange" );
            dojo.subscribe( 'majTricksWin', this, "notif_majTricksWin" );
            dojo.subscribe( 'initPannel', this, "notif_initPannel" );
            dojo.subscribe( 'score', this, "notif_score" );
            dojo.subscribe( 'majLastTrickWin', this, "notif_majLastTrickWin" );
            dojo.subscribe( 'removeLastTrickWin', this, "notif_removeLastTrickWin" );
            dojo.subscribe( 'majResumeScoreEndOfTurn', this, "notif_majResumeScoreEndOfTurn" );
            dojo.subscribe( 'majResumeScoreEndOfRound', this, "notif_majResumeScoreEndOfRound" );
            dojo.subscribe( 'majResumeScoreAfterSorcier', this, "notif_majResumeScoreAfterSorcier" );
            dojo.subscribe( 'majModalResultScoreRound', this, "notif_majModalResultScoreRound" );

            

            this.notifqueue.setSynchronous( 'endTurn', 1000 );
            
        },  
        
        notif_playCard: function(notif) {
            
            const card = notif.args.card_play;
            // Add the card to the table
            this.table[card.id] = card; // remplacer l'indice par table.length si ça sert à quelque chose...
            const div_id = this.player_id == card.location_arg ? `my_cards_item_${card.id}` : undefined;
            const card_type = this.getStockCardType(card);
            const player = this.players[card.location_arg];
            this.tableStock.addToStockWithId(card_type, card.id, div_id);
            const card_div = document.getElementById('table_cards_item_' + card.id);
            dojo.place('<div class="player-title" style="color: #' + player.color + '">' + player.name + '</div>', card_div);

            if (notif.args.momie == 1)
            {
                this.addMomieTable(card.id, notif.args.type_last_card_win, notif.args.typearg_last_card_win);
            }

            if (notif.args.clone == 1)
            {
                this.addCloneTable(card.id, notif.args.type_last_card, notif.args.typearg_last_card);
            }

            // Destroy the card for the current player
            if (this.player_id == card.location_arg) {

                this.handStock.removeFromStockById(card.id);
            }

            
            this.addTooltipsCard("table_cards_item_"+card.id, card.type, card.type_arg, card.type_arg_2);

        },

        notif_removeCards: async function(notif) {
            // chaque joueur reçoît des cartes lors de la nouvelle manche
            Object.values(notif.args.cards).forEach(card => {
                // Destroy cards for the current player
                if (this.player_id == card.location_arg) {

                    this.handStock.removeFromStockById(card.id);
                }
            });
        },

        notif_endTurn: function(notif) {
            const winner_id = notif.args.winner_id;
            setTimeout(() => {
            this.tableStock.removeAllTo('overall_player_board_' + winner_id);
            }, "1000");
   
        },

        notif_ChoiceQuest: function(notif) {
            const questremove = notif.args.questremove;
            var card = document.getElementById('questcardmodal'+questremove);
            var modal_round_container = document.getElementById("modal_round_container");
            modal_round_container.classList.add("hidden");

            card.remove();

            this.resume_quest_card[this.no_round] = notif.args.quest;
            this.detailScore(this.no_round);
           
 
        },

        notif_majRound: function(notif) {

            if(notif.args.new_round <= 7)
            {
                document.getElementById('nb_round').innerText = _('Round')+' : ' + notif.args.new_round + ' / 7';
            }
            else
            {
                document.getElementById('nb_round').innerText = _('Tiebreaker Round');
            }
            
            
        },

        notif_drawCards: async function(notif) {
            // chaque joueur reçoît des cartes lors de la nouvelle manche
            Object.values(notif.args.cards).forEach(card => {
                const card_type = this.getStockCardType(card);
                this.handStock.addToStockWithId(card_type, card.id, undefined);
                dojo.query("#my_cards_item_"+card.id).connect('onclick', this, 'onSelect' )
                
                this.addTooltipsCard("my_cards_item_"+card.id, card.type, card.type_arg, card.type_arg_2);
            });

            
        },


        notif_majModal: function(notif) {
            const special = document.getElementById("specialcardmodal");
            special.remove();
            const quest1 = document.getElementById("questcardmodal1");
            if(quest1){
                quest1.remove();
            }
            const quest2 = document.getElementById("questcardmodal2");
            if(quest2)
            {
                quest2.remove();
            }

            
            this.setupSpecialModal(notif.args.active_special_card);
            this.setupQuestModal(notif.args.active_quest_card);
            

            var modal_round_container = document.getElementById("modal_round_container");
            modal_round_container.classList.remove('hidden');

            dojo.query(".questcardmodal").connect('onclick', this, 'onSelect' );

            var modal_resume_round_container = document.getElementById("modal_resume_round_container");
            if (!modal_resume_round_container.classList.contains("hidden")) {
                modal_resume_round_container.classList.add("hidden");
            }
            
 
        },

        notif_momieChange: function(notif) {
            this.addMomie(notif.args.trick, notif.args.momie_id, notif.args.momie_player, notif.args.type_last_card_win, notif.args.typearg_last_card_win)
   
        },

        notif_showPlayersModal: function(notif) {
            this.addPlayersModal(notif.args.players, notif.args.player);
        },

        notif_removePlayersModal: function(notif) {
            this.removePlayersModal();
        },

        notif_showFlecheModal: function(notif) {
            this.addFlecheModal();
        },

        notif_removeFlecheModal: function(notif) {
            this.removeFlecheModal();
        },

        notif_showPreviousTrick: function(notif) {
            this.addPreviousTrickModal(notif.args.cards);
        },

        notif_removePreviousTrick: function(notif) {
            this.removePreviousTrick();
        },

        notif_cloneChange: function(notif) {

            this.addClone(notif.args.clone_id, notif.args.clone_player, notif.args.type_last_card, notif.args.typearg_last_card)
   
        },

        notif_majTricksWin: function(notif) {

            var element = document.getElementById('trick_win_'+notif.args.player);
            element.textContent = notif.args.newtricks; 

   
        },

        notif_initPannel: function(notif) {

            var element = document.getElementById('trick_win_'+notif.args.player);
            element.textContent = 0; 

   
        },

        notif_score: function( notif ){
    
            this.scoreCtrl[ notif.args.player ].toValue( notif.args.score );

        },

        notif_majLastTrickWin: function( notif ){

            var parent = document.getElementById("last_win_container_"+notif.args.player);
            parent.textContent = '';
    
            for(index in notif.args.trick)
            {
                var type = notif.args.trick[index].type;
                var type_arg = notif.args.trick[index].type_arg;

                this.addLastWinTrickCard(notif.args.player, type, type_arg);
            }

        },

        notif_removeLastTrickWin: function( notif ){
    
            var parent = document.getElementById("last_win_container_"+notif.args.player);
            parent.textContent = '';

        },

        notif_majResumeScoreEndOfTurn: function( notif ){
    
            this.no_round = parseInt(notif.args.no_round);
            this.no_trick = parseInt(notif.args.no_trick);
            this.view_round = this.no_round;

            this.round_result[this.no_round][this.no_trick]['cards_played'] = notif.args.cards_played;
            this.round_result[this.no_round][this.no_trick]['winner'] = notif.args.winner;

            if(this.no_trick < this.tricks_max)
            {
                this.round_result[this.no_round][this.no_trick + 1] = {
                    cards_played: [],
                    winner: null
                };
            }
                        
            this.detailScore(this.no_round);

        },

        notif_majResumeScoreEndOfRound: function (notif) {

            this.no_round = this.no_round + 1;
            this.view_round = this.no_round;

            // Initialisation du round
            this.round_result[this.no_round] = {};

            // Initialisation du trick 1
            this.round_result[this.no_round][1] = {
                cards_played: [],
                winner: null
            };

            this.detailScore(this.no_round);
        },

        notif_majResumeScoreAfterSorcier: function (notif) {

            var no_round = parseInt(notif.args.no_round);
            var no_trick = parseInt(notif.args.no_trick);
            this.view_round = this.no_round;

            this.round_result[no_round][no_trick]['cards_played'] = notif.args.cards_played;
            this.round_result[no_round][no_trick]['winner'] = notif.args.winner;


            this.detailScore(no_round);
        },

        notif_majModalResultScoreRound: function (notif) {

            var no_round = parseInt(notif.args.no_round);
            
            this.resume_score[no_round] = notif.args.result_score_round;

            this.detailScore(no_round);
        },



        



   });             
});
