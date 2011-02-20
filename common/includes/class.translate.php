<?php
/*
 * $Date$
 * $Revision$
 * $HeadURL$
 */

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
                            'Hinterlassene Gegenst'.chr(228).'nde:', 'Anz.:', 'Unbekannt', 'Dronenhangar', 'Drohnenhangar', 
			    'Mond:', 'Kapsel');

            $replace = array('Victim:','Alliance: None','Alliance: None','Alliance: None',
                     'Alliance: None','Alliance: None','Alliance: None','Alliance:',
                            'Faction: None','Faction: None','Faction: None',
                     'Faction: None','Faction: None','Faction: None','Faction:',
                     'Destroyed items','Destroyed:', 'Security:',
                            'Involved parties:', 'Qty:', 'Corp:', '(Cargo)', 'Ship:', 'Weapon:','(In Container)',
                            'Damage Done:', 'Damage Taken:', '(laid the final blow)',
                            'Dropped items:', 'Qty:', 'Unknown', 'Drone Bay', 'Drone Bay', 
			    'Moon:', 'Capsule');

            $this->killmail_ = str_replace($search, $replace, $this->killmail_);
            return  $this->killmail_;
        }
        if ($this->language_ == 'russian')
        {
            $search = array('Жертва:','Альянс: НЕТ','Альянс: нет','Альянс: Нет', 'Альянс:', 'Имя:',
                            'Фракция: Неизвестно','Фракция: НЕТ','Фракция: нет','Фракция: Нет', 'Фракция:',
                     'Уничтоженные предметы:', 'Уничтожено:', 'Уровень безопасности:', 'Система:',
                            'Участники:','кол-во:','Корпорация:','(Груз)', 'Корабль:','Оружие:','(В контейнере)',
                            'Нанесенный ущерб:','Полученный ущерб:', '(нанес последний удар)',
                            'Сброшенные предметы:', 'кол-во:', 'Неизвестно', 'Отсек дронов', 'Луна:');

            $replace = array('Victim:','Alliance: None','Alliance: None','Alliance: None','Alliance:', 'Name:',
                            'Faction: None','Faction: None','Faction: None','Faction: None', 'Faction:',
                     'Destroyed items:','Destroyed:', 'Security:', 'System:',
                            'Involved parties:', 'Qty:', 'Corp:', '(Cargo)', 'Ship:', 'Weapon:','(In Container)',
                            'Damage Done:', 'Damage Taken:', '(laid the final blow)',
                            'Dropped items:', 'Qty:', 'Unknown', 'Drone Bay', 'Moon:');

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
                        'Guristas Thor Torpedo I', 'DDO Photometry I Targeting Interference',
                        'F-392 Baker Nunn Targeting Scrambler', 'Balmer Series Targeting Inhibitor I',
                        "'Abandon' Targeting Disruptor I", 'Snoop 3AU Scanner Probe I', 'Fathom 12AU Scanner Probe I');
            $replace = array('Civilian Miner', 'Astrometric Triangulation',
                        'Core Probe Launcher I', 'Core Probe Launcher I Blueprint',
                        'Expanded Probe Launcher I', 'Expanded Probe Launcher I Blueprint',
                        'Astrometric Acquisition','Sisters Expanded Probe Launcher',
                        'Sisters Expanded Probe Launcher Blueprint',  'Sisters Core Probe Launcher',
                        'Sisters Core Probe Launcher Blueprint', 'Guristas Doom Torpedo',
                        'Guristas Purgatory Torpedo', 'Guristas Rift Torpedo',
                        'Guristas Thor Torpedo', 'DDO Photometry Tracking Disruptor I',
                        'F-392 Baker Nunn Tracking Disruptor I', 'Balmer Series Tracking Disruptor I',
                        "'Abandon' Tracking Disruptor I", 'Snoop Scanner Probe I', 'Fathom Scanner Probe I');
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
                $replace[$i] = '\1Large '. $search[$i];
            }

            $ssearch = array();
            for($i = 0; $i < count($search); $i++) {
                $ssearch[$i] = '/([^ ])'. $search[$i].'/';
            }

            $this->killmail_ = preg_replace($ssearch, $replace, $this->killmail_);
        }

        if($this->language_ == 'dominion')
        {
            $search = array('/Amarr Navy/', '/Gallente Federation/');
            $replace = array('Imperial Navy', 'Federation Navy');
            $this->killmail_ = preg_replace($search, $replace, $this->killmail_);
        }

	if($this->language_ == 'dom11')
	{
	    $search = 'Eifyr and Co ';
	    $replace = 'Eifyr and Co. ';
	    
            $this->killmail_ = str_replace($search, $replace, $this->killmail_);
	}

        return $this->killmail_;
    }
}

?>
