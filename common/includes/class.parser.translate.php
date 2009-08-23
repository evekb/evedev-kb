<?php
class Translate
{
    function uchr($codes) { //converts characterset code-pages to ascii-compatible types
        if (is_scalar($codes)) $codes= func_get_args();
        $str= '';
        foreach ($codes as $code) $str.= html_entity_decode('&#'.$code.';',ENT_NOQUOTES,'UTF-8');
        return $str;
    }

    function Translate($language)
    {
        $this->language_ = $language;
    }

    function getTranslation($killmail)
    {
        $this->killmail_ = $killmail;
        if ($this->language_ == 'german')
        {
            $this->killmail_ = str_replace(array(chr(195).chr(182), chr(195).chr(164)), array(chr(246), chr(228)), $this->killmail_);

            $search = array('Ziel:','Allianz: KEINE','Allianz: keine','Allianz: Keine',
                     'Allianz: NICHTS','Allianz: nichts','Allianz: Nichts','Allianz:',
                            'Fraktion: KEINE','Fraktion: keine','Fraktion: Keine',
                     'Fraktion: NICHTS','Fraktion: nichts','Fraktion: Nichts','Fraktion:',
                     'Zerst'.chr(246).'rte Gegenst'.chr(228).'nde', 'Zerst'.chr(246).'rt:', 'Sicherheit:',
                            'Beteiligte Parteien:','Anz:','Corporation:','(Fracht)', 'Schiff:','Waffe:','(Im Container)',
                            'Verursachter Schaden:','Erlittener Schaden:', '(gab den letzten Schuss ab)',
                            'Hinterlassene Gegenst'.chr(228).'nde:', 'Anz.:', 'Unbekannt', 'Dronenhangar', 'Drohnenhangar', 'Mond:');

            $replace = array('Victim:','Alliance: None','Alliance: None','Alliance: None',
                     'Alliance: None','Alliance: None','Alliance: None','Alliance:',
                            'Faction: None','Faction: None','Faction: None',
                     'Faction: None','Faction: None','Faction: None','Faction:',
                     'Destroyed items','Destroyed:', 'Security:',
                            'Involved parties:', 'Qty:', 'Corp:', '(Cargo)', 'Ship:', 'Weapon:','(In Container)',
                            'Damage Done:', 'Damage Taken:', '(laid the final blow)',
                            'Dropped items:', 'Qty:', 'Unknown', 'Drone Bay', 'Drone Bay', 'Moon:');

            $this->killmail_ = str_replace($search, $replace, $this->killmail_);
            return  $this->killmail_;
        }
        if ($this->language_ == 'russian')
        {
            //translate the codespace to some terrible ascii equivalent. Heaven help us if CCP decide to allow unicode pilot names :(
            $russian_codes = array($this->uchr(1040),$this->uchr(1072),$this->uchr(1041),$this->uchr(1073),$this->uchr(1042),$this->uchr(1074)
                            ,$this->uchr(1043),$this->uchr(1075),$this->uchr(1044),$this->uchr(1076),$this->uchr(1045),$this->uchr(1077)
                            ,$this->uchr(1046),$this->uchr(1078),$this->uchr(1047),$this->uchr(1079),$this->uchr(1048),$this->uchr(1080)
                            ,$this->uchr(1049),$this->uchr(1081),$this->uchr(1050),$this->uchr(1082),$this->uchr(1051),$this->uchr(1083)
                            ,$this->uchr(1052),$this->uchr(1084),$this->uchr(1053),$this->uchr(1085),$this->uchr(1054),$this->uchr(1086)
                            ,$this->uchr(1055),$this->uchr(1087),$this->uchr(1056),$this->uchr(1088),$this->uchr(1057),$this->uchr(1089)
                            ,$this->uchr(1058),$this->uchr(1090),$this->uchr(1059),$this->uchr(1091),$this->uchr(1060),$this->uchr(1092)
                            ,$this->uchr(1061),$this->uchr(1093),$this->uchr(1062),$this->uchr(1094),$this->uchr(1063),$this->uchr(1095)
                            ,$this->uchr(1064),$this->uchr(1096),$this->uchr(1065),$this->uchr(1097),$this->uchr(1066),$this->uchr(1098)
                            ,$this->uchr(1067),$this->uchr(1099),$this->uchr(1068),$this->uchr(1100),$this->uchr(1069),$this->uchr(1101)
                            ,$this->uchr(1070),$this->uchr(1102),$this->uchr(1071),$this->uchr(1103));

            $roman_codes = array('A', 'a', 'B', 'b', 'C', 'c', 'D', 'd', 'E', 'e', 'F', 'f',
                                 'G', 'g', 'H', 'h', 'I', 'i', 'J', 'j', 'K', 'k', 'J', 'j',
                                 'L', 'l', 'M', 'm', 'N', 'n', 'O', 'o', 'P', 'p', 'Q', 'q',
                                 'R', 'r', 'S', 's', 'T', 't', 'U', 'u', 'V', 'v', 'W', 'w',
                                 'X', 'x', 'Y', 'y', 'Z', 'z', '!', '1', '@', '2', '#', '3',
                                 '$', '4', '%', '5', '^', '6');

            $this->killmail_ = str_replace($russian_codes, $roman_codes, $this->killmail_);

            //utter gibberish ahoy! :)
            $search = array('Gfprca:','Aj25mq: MFR','Aj25mq: mfr','Aj25mq: Mfr', 'Aj25mq:', 'Il5:',
                            'Tpakvi5: MFR','Tpakvi5: mfr','Tpakvi5: Mfr', 'Tpakvi5:',
                     'Smiwrngfmm1f nbzfkr1:', 'Smiwrngfmn:', 'Bfhnoaqmnqr2:', 'Qiqrfla:',
                            'Cncjfwfmn oiparnc:','Anz:','Knponpavi5:','(Dpsh)', 'Knpabj2:','Cnnpsgfmi5:','(C knmrfjmfpf)',
                            'Mamnqil1j spnm:','Syfpb:', '(mamfq pfxa4yij seap)',
                            'Qbpnxfmm1f nbzfkr1:', 'K-cn:', 'Unbekannt', 'Nrqfk epnmnc');

            $replace = array('Victim:','Alliance: None','Alliance: None','Alliance: None','Alliance:', 'Name:',
                            'Faction: None','Faction: None','Faction: None', 'Faction:',
                     'Destroyed items:','Destroyed:', 'Security:', 'System:',
                            'Involved parties:', 'Qty:', 'Corp:', '(Cargo)', 'Ship:', 'Weapon:','(In Container)',
                            'Damage Done:', 'Damage Taken:', '(laid the final blow)',
                            'Dropped items:', 'Qty:', 'Unknown', 'Drone Bay');

            $this->killmail_ = str_replace($search, $replace, $this->killmail_);

            return  $this->killmail_;
        }
        if ($this->language_ == 'prermr')
        {
            $search = array('Corporation:','Destroyed Type:','Solar System:', 'System Security Level:', 'Security Status:', 'Ship Type:', 'Weapon Type:', '(Fitted - Medium slot)', '(Fitted - Low slot)', '(Fitted - High slot)');
            $replace = array('Corp:','Destroyed:', 'System:', 'Security:', 'Security:', 'Ship:', 'Weapon:', '', '', '');
            $this->killmail_ = str_replace($search, $replace, $this->killmail_);
            $position = strpos($this->killmail_, 'Destroyed items:');
            if ($position !== false)
            {
                $destroyed = explode("\n", strstr($this->killmail_, 'Destroyed items:'));
                $i = 0;
                $num = count($destroyed);
                while ($i < $num)
                {
                    $destroyed[$i] = trim($destroyed[$i]);

                    $itempos = strpos($destroyed[$i], 'Type: ');
                    if ($itempos !== false)
                    {
                        $destroyed[$i] = substr($destroyed[$i], $itempos+6);
                        if (isset($destroyed[$i+1]))
                        {
                            $quantitypos = strstr($destroyed[$i+1], 'Quantity: ');
                            if ($quantitypos !== false)
                            {
                                $qty = ', Qty: '.substr($destroyed[$i+1], $quantitypos+10);
                                $pos = strpos($destroyed[$i], '(');
                                if ($pos !== false)
                                {
                                    $destroyed[$i] = trim(substr($destroyed[$i], 0, $pos)).$qty.' '.substr($destroyed[$i], $pos);
                                }
                                else
                                {
                                    $destroyed[$i] .= $qty;
                                }
                                unset($destroyed[$i+1]);
                                $i++;
                            }
                        }
                    }
                    else
                    {
                        unset($destroyed[$i]);
                    }
                    $i++;
                }
                $this->killmail_ = substr($this->killmail_, 0, $position).'Destroyed items: '."\n\n\n".join("\n", $destroyed)."\n";
            }
            return $this->killmail_;
        }

        if($this->language_ == 'preqr')
        {
            $search = array('Faint Epsilon Warp Prohibitor I', 'Initiated Harmonic Warp Jammer I',
                        'J5b Phased Prototype Warp Inhibitor I', 'J5 Prototype Warp Inhibitor I',
                        'Fleeting Warp Scrambler I', 'Faint Warp Prohibitor I', 'Initiated Warp Jammer I');
            $replace = array('Faint Epsilon Warp Scrambler I', 'Initiated Harmonic Warp Scrambler I',
                        'J5b Phased Prototype Warp Scrambler I', 'J5 Prototype Warp Disruptor I',
                        'Fleeting Warp Disruptor I', 'Faint Warp Disruptor I', 'Initiated Warp Disruptor I');
            $this->killmail_ = str_replace($search, $replace, $this->killmail_);
        }
        if($this->language_ == 'apoc')
        {
            $search = array('Basic Miner', 'Signal Acquisition',
                        'Scan Probe Launcher I', 'Scan Probe Launcher I Blueprint',
                        'Recon Probe Launcher I', 'Recon Probe Launcher I Blueprint',
                        'Astrometric Triangulation', 'Sisters Recon Probe Launcher',
                        'Sisters Recon Probe Launcher Blueprint', 'Sisters Scan Probe Launcher',
                        'Sisters Scan Probe Launcher Blueprint', 'Guristas Doom Torpedo I',
                        'Guristas Purgatory Torpedo I', 'Guristas Rift Torpedo I',
                        'Guristas Thor Torpedo I');
            $replace = array('Civilian Miner', 'Astrometric Triangulation',
                        'Core Probe Launcher I', 'Core Probe Launcher I Blueprint',
                        'Expanded Probe Launcher I', 'Expanded Probe Launcher I Blueprint',
                        'Astrometric Acquisition','Sisters Expanded Probe Launcher',
                        'Sisters Expanded Probe Launcher Blueprint',  'Sisters Core Probe Launcher',
                        'Sisters Core Probe Launcher Blueprint', 'Guristas Doom Torpedo',
                        'Guristas Purgatory Torpedo', 'Guristas Rift Torpedo',
                        'Guristas Thor Torpedo');
            $this->killmail_ = str_replace($search, $replace, $this->killmail_);
        }

        if($this->language_ == 'apoc15')
        {
            $search = array('Anti-EM Pump', 'Anti-Explosive Pump', 'Anti-Kinetic Pump',
                'Anti-Thermic Pump', 'Trimark Armor Pump', 'Auxiliary Nano Pump',
                'Nanobot Accelerator', 'Remote Repair Augmentor', 'Salvage Tackle',
                'Core Defence Capacitor Safeguard', 'Anti-EM Screen Reinforcer',
                'Anti-Explosive Screen Reinforcer', 'Anti-Kinetic Screen Reinforcer',
                'Anti-Thermal Screen Reinforcer', 'Core Defence Field Purger',
                'Core Defence Operational Solidifier', 'Core Defence Field Extender',
                'Core Defence Charge Economizer', 'Shield Transporter Rig', 'Energy Discharge Elutriation',
                'Energy Ambit Extension', 'Energy Locus Coordinator', 'Energy Metastasis Adjuster',
                'Algid Energy Administrations Unit', 'Energy Burst Aerator', 'Energy Collision Accelerator',
                'Hybrid Discharge Elutriation', 'Hybrid Ambit Extension', 'Hybrid Locus Coordinator',
                'Hybrid Metastasis Adjuster', 'Algid Hybrid Administrations Unit', 'Hybrid Burst Aerator',
                'Hybrid Collision Accelerator', 'Projectile Cache Distributor', 'Projectile Ambit Extension',
                'Projectile Locus Coordinator', 'Projectile Metastasis Adjuster', 'Projectile Consumption Elutriator',
                'Projectile Burst Aerator', 'Projectile Collision Accelerator', 'Drone Control Range Augmentor',
                'Drone Repair Augmentor', 'Drone Scope Chip', 'Drone Speed Augmentor', 'Drone Durability Enhancer',
                'Drone Mining Augmentor', 'Sentry Damage Augmentor', 'EW Drone Range Augmentor',
                'Stasis Drone Augmentor', 'Drone Damage Rig', 'Hydraulic Bay Thrusters',
                'Launcher Processor Rig', 'Warhead Rigor Catalyst', 'Rocket Fuel Cache Partition',
                'Missile Guidance System Rig', 'Bay Loading Accelerator', 'Warhead Flare Catalyst',
                'Warhead Calefaction Catalyst', 'Signal Disruption Amplifier', 'Emission Scope Sharpener',
                'Memetic Algorithm Bank', 'Liquid Cooled Electronics', 'Gravity Capacitor Upgrade',
                'Processor Overclocking Unit', 'Capacitor Control Circuit', 'Egress Port Maximizer',
                'Powergrid Subroutine Maximizer', 'Semiconductor Memory Cell', 'Ancillary Current Router',
                'Dynamic Fuel Valve', 'Low Friction Nozzle Joints', 'Auxiliary Thrusters',
                'Engine Thermal Shielding', 'Propellant Injection Vent', 'Warp Core Optimizer',
                'Hyperspatial Velocity Optimizer', 'Polycarbon Engine Housing', 'Cargohold Optimization',
                'Targeting Systems Stabilizer', 'Targeting System Subcontroller', 'Ionic Field Projector',
                'Signal Focusing Kit', 'Particle Dispersion Augmentor', 'Particle Dispersion Projector',
                'Inverted Signal Field Projector', 'Tracking Diagnostic Subroutines', 'Sensor Strength Rig');

            $replace = array();
            for($i = 0; $i < count($search); $i++) {
                $replace[$i] = 'Large '. $search[$i];
            }

            $this->killmail_ = str_replace($search, $replace, $this->killmail_);
        }

        return $this->killmail_;
    }
}

?>
