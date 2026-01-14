<?php

$this->_CARDS = [
    110 => [
        "name" => clienttranslate('Druid'),
        "text" => clienttranslate("After this trick, swap your hand for any other player's. This card has no effect if played in the final trick.")
    ],
    150 => [
        "name" => clienttranslate('Musician'),
        "text" => clienttranslate('The player who wins this card becomes the dealer for the next deal.')
    ],
    1111 => [
        "name" => clienttranslate('Flag Bearer'),
        "text" => clienttranslate("<b>+3</b> <div class='log_pv'></div> for the player who wins this card.")
    ],
    1112 => [
        "name" => clienttranslate('Tinkerer'),
        "text" => clienttranslate("<b>+3</b> <div class='log_pv'></div> for the player who wins the next trick. This card has no effect if played in the final trick.")
    ],
    210 => [
        "name" => clienttranslate('Joker'),
        "text" => clienttranslate("<b>+3</b> <div class='log_pv'></div> if you play this card during the final trick, whether you win it or not.")
    ],
    250 => [
        "name" => clienttranslate('Musician'),
        "text" => clienttranslate('Choose the Quest for this deal.')
    ],
    2111 => [
        "name" => clienttranslate('Flag Bearer'),
        "text" => clienttranslate('After this trick, choose whether all players must give their hand to the player on their left or right. This card has no effect if played in the final trick.')
    ],
    2112 => [
        "name" => clienttranslate('Enchanter'),
        "text" => clienttranslate("The player who wins this card doubles <div class='log_pv'></div> they win or lose during this deal.")
    ],
    310 => [
        "name" => clienttranslate('Scout'),
        "text" => clienttranslate("<b>+1</b> <div class='log_pv'></div> for each <b>J</b>, <b>Q</b>, <b>K</b> and <b>A</b> played in the same trick as this card, whether you win it or not.")
    ],
    350 => [
        "name" => clienttranslate('Musician'),
        "text" => clienttranslate('Lead the first trick with any card.')
    ],
    3111 => [
        "name" => clienttranslate('Shaman'),
        "text" => clienttranslate("<b>-3</b> <div class='log_pv'></div> for the player who wins this card.")
    ],
    3112 => [
        "name" => clienttranslate('Flag Bearer'),
        "text" => clienttranslate("Reveal this card right after <b>Choose a Quest</b>. Take 2 random cards from any player's hand, then give them any 2 cards from your hand.")
    ],
    410 => [
        "name" => clienttranslate('Dragon'),
        "text" => clienttranslate('You can always play the <b>Dragon</b>, even if you have cards of the required suit. You win the trick, unless someone plays an <b>A</b>. If A of the suit led is played, it wins the trick. If it is not played, the last A played in that trick wins it. If you lead a trick with Dragon, the suit led is that of the next card played.')
    ],
    420 => [
        "name" => clienttranslate('Mummy'),
        "text" => clienttranslate('The <b>Mummy</b> copies the winning card from the previous trick. It has the same color and value. Cannot be played on the first trick of a round.')
    ],
    430 => [
        "name" => clienttranslate('Puppeteer'),
        "text" => clienttranslate('The <b>Puppeteer</b> has the same color as the previous card played, though its value is considered to be slightly higher. You cannot start the first trick of a round with Puppeteer.')
    ],
    440 => [
        "name" => clienttranslate('Sorcerer'),
        "text" => clienttranslate('Play this card between two tricks: choose a card from the previous trick, add it to your hand, and replace it with the <b>Sorcerer</b>.')
    ],
    450 => [
        "name" => clienttranslate('Excalibur'),
        "text" => clienttranslate('You can only play <b>Excalibur</b> if you do not have the required suit. You win the trick.')
    ]
    
];

$this->_QUESTS = [
    11 => [
        "name" => clienttranslate('Lucky Run'),
        "text" => clienttranslate("+2 <div class='log_pv'></div> for each trick in the longest run of tricks you win in a row.")
    ],
    12 => [
        "name" => clienttranslate('Bad Run'),
        "text" => clienttranslate("-2 <div class='log_pv'></div> for each trick in the longest run of tricks you win in a row.")
    ],

    21 => [
        "name" => clienttranslate('Save the Rearguard'),
        "text" => clienttranslate("+2 <div class='log_pv'></div> for each of the last four tricks.")
    ],
    22 => [
        "name" => clienttranslate('Sacrifice the Rearguard'),
        "text" => clienttranslate("-2 <div class='log_pv'></div> for each of the last four tricks.")
    ],

    31 => [
        "name" => clienttranslate('Three Quarters'),
        "text" => clienttranslate("+4 <div class='log_pv'></div> for each <b>3</b> and <b>4</b>.")
    ],
    32 => [
        "name" => clienttranslate('From 6 to 7'),
        "text" => clienttranslate("+4 <div class='log_pv'></div> for each <b>6</b> and <b>7</b>.")
    ],

    41 => [
        "name" => clienttranslate("Knights' Debacle"),
        "text" => clienttranslate("-1 <div class='log_pv'></div> for each <div class='icon_quest blue'></div>.")
    ],
    42 => [
        "name" => clienttranslate("Goblin Defeat"),
        "text" => clienttranslate("-1 <div class='log_pv'></div> for each <div class='icon_quest red'></div>.")
    ],

    51 => [
        "name" => clienttranslate("To the Right"),
        "text" => clienttranslate("+1 <div class='log_pv'></div> for each trick won by the previous player.")
    ],
    52 => [
        "name" => clienttranslate("To the Left"),
        "text" => clienttranslate("+1 <div class='log_pv'></div> for each trick won by the next player.")
    ],

    61 => [
        "name" => clienttranslate("Good Numbers"),
        "text" => clienttranslate("Add the values (from <b>1</b> to <b>11</b>) of your cards. +5 <div class='log_pv'></div> for the player with the highest total.")
    ],
    62 => [
        "name" => clienttranslate("Bad Numbers"),
        "text" => clienttranslate("Add up the values (from <b>1</b> to <b>11</b>) of your cards. -5 <div class='log_pv'></div> for the player with the highest total.")
    ],

    71 => [
        "name" => clienttranslate("Most Victories"),
        "text" => clienttranslate("+1 <div class='log_pv'></div> for each trick.")
    ],
    72 => [
        "name" => clienttranslate("Fewest Victories"),
        "text" => clienttranslate("-1 <div class='log_pv'></div> for each trick.")
    ],

    81 => [
        "name" => clienttranslate("Good Things Come in 3s"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if you win exactly 3 tricks.")
    ],
    82 => [
        "name" => clienttranslate("There Can Be Only One"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if you win exactly 1 trick.")
    ],

    91 => [
        "name" => clienttranslate("Four by Four"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if you win exactly 4 tricks.")
    ],
    92 => [
        "name" => clienttranslate("Two by Two"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if you win exactly 2 tricks.")
    ],

    101 => [
        "name" => clienttranslate("Evens"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if you win an even number of tricks."),
        "text2" => clienttranslate("0 is not an even number")
    ],
    102 => [
        "name" => clienttranslate("Odds"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if you win an odd number of tricks"),
        "text2" => clienttranslate("0 is not an odd number")
    ],

    111 => [
        "name" => clienttranslate("All for One"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if no other player wins the same number of tricks as you.")
    ],
    112 => [
        "name" => clienttranslate("One for All"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if at least one other player wins the same number of tricks as you.")
    ],

    121 => [
        "name" => clienttranslate("Knightly Victory Against Dwarves"),
        "text" => clienttranslate("+1 <div class='log_pv'></div> for each <div class='icon_quest blue'></div>."),
        "text2" => clienttranslate("-1 <div class='log_pv'></div> for each <div class='icon_quest green'></div>.")
    ],
    122 => [
        "name" => clienttranslate("Dwarven Victory Against Knights"),
         "text" => clienttranslate("+1 <div class='log_pv'></div> for each <div class='icon_quest green'></div>."),
        "text2" => clienttranslate("-1 <div class='log_pv'></div> for each <div class='icon_quest blue'></div>.")
    ],

    131 => [
        "name" => clienttranslate("Capture the Kings"),
        "text" => clienttranslate("+3 <div class='log_pv'></div> for each <b>K</b>."),
        "text2" => clienttranslate("-3 <div class='log_pv'></div> for each <b>Q</b>.")
    ],
    132 => [
        "name" => clienttranslate("Capture the Queens"),
        "text" => clienttranslate("+3 <div class='log_pv'></div> for each <b>Q</b>."),
        "text2" => clienttranslate("-3 <div class='log_pv'></div> for each <b>K</b>.")
    ],

    141 => [
        "name" => clienttranslate("Eeny, Meeny"),
        "text" => clienttranslate("+3 <div class='log_pv'></div> for the 1st player to win their 3rd trick."),
        "text2" => clienttranslate("+4 <div class='log_pv'></div> for the 2nd player to win their 3rd trick, etc.")
    ],
    142 => [
        "name" => clienttranslate("... Miney Mo"),
         "text" => clienttranslate("-3 <div class='log_pv'></div> for the 1st player to win their 3rd trick."),
        "text2" => clienttranslate("-4 <div class='log_pv'></div> for the 2nd player to win their 3rd trick, etc.")
    ],

    151 => [
        "name" => clienttranslate("Triplets"),
        "text" => clienttranslate("+2 <div class='log_pv'></div> for each three-of-a-kind of <b>J</b>, <b>Q</b>, <b>K</b> or <b>A</b>."),
        "text2" => clienttranslate("+1 <div class='log_pv'></div> for each three-of-a-kind of other values.")
    ],
    152 => [
        "name" => clienttranslate("1, 2, and…"),
        "text" => clienttranslate("-2 <div class='log_pv'></div> for each three-of-a-kind of <b>J</b>, <b>Q</b>, <b>K</b> or <b>A</b>."),
        "text2" => clienttranslate("-1 <div class='log_pv'></div> for each three-of-a-kind of other values.")
    ],

    161 => [
        "name" => clienttranslate("Grand Slam"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if you win at least 4 tricks in a row.")
    ],
    162 => [
        "name" => clienttranslate("Utter Chaos"),
        "text" => clienttranslate("+5 <div class='log_pv'></div> if you lose at least 4 tricks in a row")
    ],

    171 => [
        "name" => clienttranslate("Elite Army"),
        "text" => clienttranslate("Gain as many <div class='log_pv'></div> as the number of cards of the color you have the least of.")
    ],
    172 => [
        "name" => clienttranslate("Massive Army"),
        "text" => clienttranslate("Gain as many <div class='log_pv'></div> as the number of cards of the color you have the most of.")
    ],

    181 => [
        "name" => clienttranslate("Garden Gnomes"),
        "text" => clienttranslate("Gain as many <div class='log_pv'></div> as your lowest <div class='icon_quest green'></div> value. <div class='icon_quest jackg'></div>, <div class='icon_quest queeng'></div>, <div class='icon_quest kingg'></div> and <div class='icon_quest asg'></div> do not have any value.")
    ],
    182 => [
        "name" => clienttranslate("Nobles"),
        "text" => clienttranslate("Gain as many <div class='log_pv'></div> as your lowest <div class='icon_quest blue'></div> value. <div class='icon_quest jackb'></div>, <div class='icon_quest queenb'></div>, <div class='icon_quest kingb'></div> and <div class='icon_quest asb'></div> do not have any value.")
    ],

    191 => [
        "name" => clienttranslate("Nobles and Knights"),
        "text" => clienttranslate("+1 <div class='log_pv'></div> for each <b>J</b>, <b>Q</b>, <b>K</b> and <b>A</b> and each <div class='icon_quest blue'></div> (so +2 <div class='log_pv'></div> for <div class='icon_quest jackb'></div>, <div class='icon_quest queenb'></div>, <div class='icon_quest kingb'></div> and <div class='icon_quest asb'></div>).")
    ],
    192 => [
        "name" => clienttranslate("Soldiers without Knights"),
        "text" => clienttranslate("-1 <div class='log_pv'></div> for each <b>J</b>, <b>Q</b>, <b>K</b> and <b>A</b> and each <div class='icon_quest blue'></div> (so -2 <div class='log_pv'></div> for <div class='icon_quest jackb'></div>, <div class='icon_quest queenb'></div>, <div class='icon_quest kingb'></div> and <div class='icon_quest asb'></div>).")
    ],

    201 => [
        "name" => clienttranslate("In Line"),
        "text" => clienttranslate("+3 <div class='log_pv'></div> if you win fewer tricks than the previous player."),
        "text2" => clienttranslate("+3 <div class='log_pv'></div> if you win more tricks than the next player.")
    ],
    202 => [
        "name" => clienttranslate("Sandwich"),
        "text" => clienttranslate("+3 <div class='log_pv'></div> if you win more tricks than the previous player."),
        "text2" => clienttranslate("+3 <div class='log_pv'></div> if you win more tricks than the next player.")
    ]

];