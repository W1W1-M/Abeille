<?php

    require_once dirname(__FILE__).'/../../../../core/php/core.inc.php';
    include_once(dirname(__FILE__).'/../../resources/AbeilleDeamon/lib/Tools.php');
    /*
    if (!isConnect('admin')) {
        throw new Exception('401 Unauthorized');
    }
     */

    
    $eqLogics = Abeille::byType('Abeille');
?>

<table class="table table-condensed tablesorter" id="table_healthAbeille">
    <thead>
        <tr>
            <th class="header" data-toggle="tooltip" title="Trier par">{{Module}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{Name}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{ID}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{Ruche}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{Address}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{IEEE}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{Statut}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{Dernière communication}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{Depuis (h)}}</th>
            <th class="header" data-toggle="tooltip" title="Trier par">{{Date création}}</th>
        </tr>
    </thead>
    <tbody>
    <?php
        // To identify duplicated objet with same IEEE
        $IEEE_Table = array();
        
        foreach ($eqLogics as $eqLogic) {

            // Module
            echo "\n\n\n\n<tr>".'<td><a href="'.$eqLogic->getLinkToConfiguration().'" style="text-decoration: none;">'.$eqLogic->getHumanName(true).'</a></td>';

            // Nom
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">'.$eqLogic->getConfiguration('icone').'</span></td>';
            
            // ID
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">'.$eqLogic->getId().'</span></td>';
            
            $parts = explode("/", $eqLogic->getLogicalId());
            
            // Ruche
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">'.$parts[0].'</span></td>';
            
            // Short Address
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">'.$parts[1].'</span></td>';
            
            // IEEE
            
            // Recupere IEEE de la Ruche/ZiGate
            $commandIEEE = $eqLogic->getCmd('info', 'IEEE-Addr');
            
            if ( $commandIEEE ) {
                $addrIEEE = $commandIEEE->execCmd();
                if (strlen($addrIEEE) > 2 ) {
                    if ( array_key_exists($addrIEEE, $IEEE_Table) ) {
                        $IEEE_Table[$addrIEEE] += 1;
                    }
                    else {
                        $IEEE_Table[$addrIEEE] = 1;
                    }
                }
            }

            if ( $eqLogic->getConfiguration('icone') == "remotecontrol" ) {
                $addrIEEE = "-";
            }
            
            if ( strlen($addrIEEE) == 16 || $addrIEEE=="-") {
                echo '<td><span class="label label-success" style="font-size : 1em; cursor : default;">'.$addrIEEE.'</span></td>';
            }
            else {
                echo '<td><span class="label label-warning" style="font-size : 1em; cursor : default;">Missing</span></td>';
            }


            // Status
            // Status Ok par defaut, apres on test et on met le status à la valeur voulue
            $status = '<span class="label label-success" style="font-size : 1em; cursor : default;">{{OK}}</span>';
            if ( (time() - strtotime($eqLogic->getStatus('lastCommunication'))) > (60*$eqLogic->getTimeout()) ) {
                 $status = '<span class="label label-warning" style="font-size : 1em; cursor : default;">Time Out Last Communication</span>';
            }
            if ( (time() - strtotime($eqLogic->getStatus('lastCommunication'))) > ((2*60*$eqLogic->getTimeout())) ) {
                $status = '<span class="label label-danger" style="font-size : 1em; cursor : default;">Time Out Last Communication</span>';
            }
            if ($eqLogic->getStatus('state') == '-') {
                $status = '<span class="label label-success" style="font-size : 1em; cursor : default;">-</span>';
            }
            if ( $eqLogic->getConfiguration('icone') == "remotecontrol" ) {
                $status = '<span class="label label-success" style="font-size : 1em; cursor : default;">-</span>';
            }
            echo '<td>'.$status.'</td>';

            // Derniere Comm
            $lastComm = '<span class="label label-warning" style="font-size : 1em; cursor : default;">No message received !!</span>';
            if ( strlen($eqLogic->getStatus('lastCommunication'))>2 ) {
                $lastComm = '<span class="label label-info" style="font-size : 1em; cursor : default;">'.$eqLogic->getStatus('lastCommunication').'</span>';
            }
            if ( $eqLogic->getConfiguration('icone') == "remotecontrol" ) {
                $lastComm = '<span class="label label-info" style="font-size : 1em; cursor : default;">-</span>';
            }
            echo '<td>'.$lastComm.'</td>';

            // Depuis
            $Depuis = '<span class="label label-info" style="font-size : 1em; cursor : default;">'.(floor((time() - strtotime($eqLogic->getStatus('lastCommunication'))) / 3600)).'</span>';
             if ( $eqLogic->getConfiguration('icone') == "remotecontrol" ) {
                 $Depuis = '<span class="label label-info" style="font-size : 1em; cursor : default;">-</span>';
             }
            //if ($eqLogic->getStatus('state') == '-') { $Depuis = '<span class="label label-info" style="font-size : 1em; cursor : default;">-</span>'; }
            echo '<td>'.$Depuis.'</td>';


            // Date Creation
            echo '<td><span class="label label-info" style="font-size : 1em; cursor : default;">'.$eqLogic->getConfiguration('createtime').'</span></td></tr>';
        }
    ?>
    </tbody>
</table>

<?php
foreach ($IEEE_Table as $IEEE=>$IEEE_Device) {
    if ($IEEE_Device>1) { echo "L'adresse ->".$IEEE."<- est dupliquée ce n'est pas normal. On ne doit avoir qu'un équipment par adresse IEEE</br>"; }
}
    ?>

<?php include_file('desktop', 'health', 'js', 'Abeille'); ?>
