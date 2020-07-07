<html>
<head>
</head>
<body>
<h1><?php echo $character['CharacterName'];?></h1>
<?php
if(!empty($character['Allegiance']) && isset($characters[$character['Allegiance']])){
    $allegiance = $characters[$character['Allegiance']];
    echo "<p><strong>Allegiance</strong>: ".getCharacterName($character['Allegiance'], TRUE);
    if($character['CrewCode'] == $allegiance['CrewCode']) echo "; leader of your Crew";
    if($allegiance['Belief_Rank'] == 3){
        $allegiance_faction = $factions[$allegiance['Faction']];
        echo "; Leader of the $allegiance_faction[Side]";
    }
    echo "</p>\n";
}

if(!empty($character['CharacterDescription'])) echo "<p>".parseText($character['CharacterDescription'])."</p>";
?>
<?php
if($character['Origin']){
    echo "<h2>Residence: $character[Origin]</h2>\n";
    if($character['Origin']=='Centralia'){
        ?><p>While many of the Huldufólk have moved on to Bloomsburg (a nearby college town) since Centralia's collapse, you're one of the Huldufólk
            who's stayed in Centralia itself. Over the years, as residents have passed away, more of the town has been shut down. At this point only
            5 residents remain in the town itself, though plenty of tourists come through to gawk at the abandoned town. As the years have passed though,
            little of the city has been accessible by the tourists, leaving only graffiti highway and the old town church. You've never truly given up
            on Centralia, and with the Mine Fire out, and today's meeting about the town's future, things may finally be turning around.</p><?php
    }
    elseif($character['Origin']=='Bloomsburg'){
        ?><p>As Centralia disintegrated as a town, many of the Huldufólk have moved onto Bloomsburg, or other locations entirely.</p>
        <p>Bloomsburg is a college town. It's not the largest college town around, but with 10,000 students it's a decent place to try to influence
        the future, for good or ill. For its size, Bloomsburg has a disproportionately large number of Huldufólk -- those who had given up on living
        in Centralia, but weren't ready to give up on it for good. The Mine fire going on and today's meeting might change finally turn things around.</p><?php
    }
    elseif($character['Origin']=='Reading'){
        ?><p>Reading, the state Capital of Pennsylvania, is a natural place for Huldufólk to gather. Besides being a moderately sized city,
        Reading provides an excellent opportunity for politically minded Huldufólk to influence the entire state. With influence over Philadelphia,
        Pittsburgh, and the rest of the state, Centralia does not normally rank highly on your list, but with the sudden extinguishing of a mine fire
        expected to last forever, Centralia suddenly has a consider amount of mortal attention, as well as yours.</p><?php
    }
}

if($character['CrewCode'] && isset($crews[$character['CrewCode']])){
    $crew = $crews[$character['CrewCode']];
    echo "<h1>Crew: $crew[Name] (led by ".getCharacterName($crew['Leader']).")</h1>";
    if($crew['Focus']){
        echo "<p><strong>Focus</strong>: ".parseText($crew['Focus'])."</p>\n";
    }
    if($crew['CrewTies']){
        echo "<p><strong>Other Crews</strong>: ".parseText($crew['CrewTies'])."</p>\n";
    }
}

if($character['Faction'] && isset($factions[$character['Faction']])){
    $faction = $factions[$character['Faction']];
    echo "<h1>Faction: $faction[Faction]";
    if($faction['Side']) echo " ($faction[Side])";
    echo "</h1>";
    if($faction['Description']){
        echo "<p>".parseText($faction['Description'])."</p>\n";
    }
    if($faction['Goals']){
        echo "<p>".parseText($faction['Goals'])."</p>\n";
    }
}
if(!empty($character['CharacterTie'])){
    echo "<h1>Character Ties</h1>\n";
    echo "<p>".parseText($character['CharacterTie'])."</p>\n";
}

if(!empty($character['PlotTie'])){
    echo "<h1>Game Ties</h1>\n";
    echo "<p>".parseText($character['PlotTie'])."</p>\n";
}
?>
</body>