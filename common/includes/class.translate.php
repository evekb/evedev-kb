<?php
/**
 * $Date$
 * $Revision$
 * $HeadURL$
 * @package EDK
 */

/**
 * @package EDK
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

            $search = array('Opfer:','Ziel:','Allianz: KEINE','Allianz: keine','Allianz: Keine',
                     'Allianz: NICHTS','Allianz: nichts','Allianz: Nichts','Allianz:',
                            'Fraktion: KEINE','Fraktion: keine','Fraktion: Keine',
                     'Fraktion: NICHTS','Fraktion: nichts','Fraktion: Nichts','Fraktion:',
                     'Zerst'.chr(246).'rte Gegenst'.chr(228).'nde', 'Zerst'.chr(246).'rt:', 'Sicherheit:',
                            'Beteiligte Parteien:','Anz:','Corporation:','(Fracht)', 'Schiff:','Waffe:','(Im Container)',
                            'Verursachter Schaden:','Erlittener Schaden:', '(gab den letzten Schuss ab)',
                            'Hinterlassene Gegenst'.chr(228).'nde:', 'Anz.:', 'Unbekannt', 'Dronenhangar', 'Drohnenhangar', 
			    'Mond:', 'Kapsel', 'Menge:');

            $replace = array('Victim:','Victim:','Alliance: None','Alliance: None','Alliance: None',
                     'Alliance: None','Alliance: None','Alliance: None','Alliance:',
                            'Faction: None','Faction: None','Faction: None',
                     'Faction: None','Faction: None','Faction: None','Faction:',
                     'Destroyed items','Destroyed:', 'Security:',
                            'Involved parties:', 'Qty:', 'Corp:', '(Cargo)', 'Ship:', 'Weapon:','(In Container)',
                            'Damage Done:', 'Damage Taken:', '(laid the final blow)',
                            'Dropped items:', 'Qty:', 'Unknown', 'Drone Bay', 'Drone Bay', 
			    'Moon:', 'Capsule', 'Qty:');

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
    
    if($this->language_ == 'cru10')
    {
        $search = array("Micro Organisms", "Ice Micro Organisms Extractor", "Barren Micro Organisms Extractor", 
            "Temperate Micro Organisms Extractor", "Oceanic Micro Organisms Extractor", "Hardwiring - Eifyr and Co 'Gunslinger' MX-0.5", 
            "Hardwiring - Eifyr and Co 'Gunslinger' MX-1.5", "Hardwiring - Eifyr and Co 'Gunslinger' MX-2.5", "Mechanic", 
            "Dual 1000mm 'Scout' I Accelerator Cannon", "Quad 3500mm Gallium I Cannon", "6x2500mm Heavy Gallium I Repeating Cannon",
            "Looking Glass Ocular Implant (right/gold)", "X5 Prototype I Engine Enervator", "Looking Glass Ocular Implant (right/gray)",
            "Looking Glass Ocular Implant (left/gold)", "Looking Glass Ocular Implant (left/gray)", "Women's 'Structure' Skirt (camoflage)",
            "AGM I Capacitor Charge Array", "Eutectic I Capacitor Charge Array", "Small Automated I Carapace Restoration",
            "Medium Automated I Carapace Restoration", "Large Automated I Carapace Restoration", "Micro F-4a Ld-Sulfate I Capacitor Charge Unit",
            "Micro Peroxide I Capacitor Power Cell", "Small F-4a Ld-Sulfate I Capacitor Charge Unit", "Small Peroxide I Capacitor Power Cell",
            "Medium F-RX Prototype I Capacitor Boost", "Large F-4a Ld-Sulfate I Capacitor Charge Unit", "Large Peroxide I Capacitor Power Cell",
            "Micro F-RX Prototype I Capacitor Boost", "Small F-RX Prototype I Capacitor Boost", "Heavy F-RX Prototype I Capacitor Boost",
            "'Regard' I Power Projector", "Small 'Knave' I Energy Drain", "EP-S Gaussian I Excavation Pulse", "Connected Scanning I CPU Uplink",
            "Linked I Sensor Network", "Phase Switching I Targeting Nexus", "Quad LiF Fueled I Booster Rockets", "LiF Fueled I Booster Rockets",
            "Catalyzed Cold-Gas I Arcjet Thrusters", "Y-T8 Overcharged Hydrocarbon I Microwarpdrive", "Monopropellant I Hydrazine Boosters", 
            "Cold-Gas I Arcjet Thrusters", "Y-S8 Hydrocarbon I Afterburners", "Medium Peroxide I Capacitor Power Cell", 
            "Medium F-4a Ld-Sulfate I Capacitor Charge Unit", "Prototype I Freight Sensors", "Prototype I Sensor Booster",
            "Alumel-Wired I Sensor Augmentation", "Reserve I Gravimetric Scanners", "Reserve I LADAR Scanners", "Reserve I Magnetometric Scanners",
            "Reserve I Multi-Frequency Scanners", "Reserve I RADAR Scanners", "Fourier Transform I Tracking Program",
            "Small Converse I Deflection Catalyzer", "'Benefactor' I Ward Reconstructor", "75mm Prototype I Gauss Gun",
            "75mm 'Scout' I Accelerator Cannon", "150mm Prototype I Gauss Gun", "150mm 'Scout' I Accelerator Cannon",
            "Dual 150mm Prototype I Gauss Gun", "Dual 150mm 'Scout' I Accelerator Cannon", "250mm Prototype I Gauss Gun",
            "250mm 'Scout' I Accelerator Cannon", "Dual 250mm Prototype I Gauss Gun", "Dual 250mm 'Scout' I Accelerator Cannon",
            "425mm Prototype I Gauss Gun", "425mm 'Scout' I Accelerator Cannon", "Prototype ECCM I Radar Sensor Cluster",
            "Prototype ECCM I Ladar Sensor Cluster", "Prototype ECCM I Gravimetric Sensor Cluster", "Prototype ECCM I Omni Sensor Cluster",
            "Prototype ECCM I Magnetometric Sensor Cluster", "Small 'Atonement' I Ward Projector", "Medium 'Atonement' I Ward Projector",
            "Micro 'Atonement' I Ward Projector", "Large 'Atonement' I Ward Projector", "125mm Light Gallium I Machine Gun",
            "125mm Light Prototype I Automatic Cannon", "150mm Light Gallium I Machine Gun", "150mm Light Prototype I Automatic Cannon",
            "200mm Light Gallium I Machine Gun", "200mm Light Prototype I Automatic Cannon", "250mm Light Gallium I Cannon",
            "250mm Light Prototype I Siege Cannon", "Dual 180mm Gallium I Machine Gun", "Dual 180mm Prototype I Automatic Cannon",
            "220mm Medium Gallium I Machine Gun", "220mm Medium Prototype I Automatic Cannon", "425mm Medium Gallium I Machine Gun",
            "425mm Medium Prototype I Automatic Cannon", "650mm Medium Gallium I Cannon", "650mm Medium Prototype I Siege Cannon",
            "Dual 425mm Gallium I Machine Gun", "Dual 425mm Prototype I Automatic Cannon", "Dual 650mm Gallium I Repeating Cannon",
            "Dual 650mm Prototype I Repeating Siege Cannon", "800mm Heavy Gallium I Repeating Cannon", "800mm Heavy Prototype I Repeating Siege Cannon",
            "1200mm Heavy Gallium I Cannon", "1200mm Heavy Prototype I Siege Cannon", "280mm Gallium I Cannon", "280mm Prototype I Siege Cannon",
            "720mm Gallium I Cannon", "720mm Prototype I Siege Cannon", "1400mm Gallium I Cannon", "1400mm Prototype I Siege Cannon",
            "'Penumbra' I White Noise ECM", "'Anointed' I EM Ward Reinforcement", "Large 'Vehemence' I Shockwave Charge",
            "Small 'Vehemence' I Shockwave Charge", "Micro 'Vehemence' I Shockwave Charge", "Medium 'Vehemence' I Shockwave Charge",
            "125mm 'Scout' I Accelerator Cannon", "125mm Prototype I Gauss Gun", "Medium Converse I Deflection Catalyzer",
            "Large Converse I Deflection Catalyzer", "X-Large Converse I Deflection Catalyzer", "Skirmish Warfare Link - Interdiction Maneuvers",
            "Information Warfare Link - Sensor Integrity", "EMP Generator", "200mm 'Scout' I Accelerator Cannon", "200mm Prototype I Gauss Gun",
            "350mm 'Scout' I Accelerator Cannon", "350mm Prototype I Gauss Gun", "'Accord' I Core Compensation", "'Repose' I Core Compensation",
            "Small 'Arup' I Remote Bulwark Reconstruction", "Small 'Solace' I Remote Bulwark Reconstruction",
            "Medium 'Arup' I Remote Bulwark Reconstruction", "Medium 'Solace' I Remote Bulwark Reconstruction",
            "Large 'Arup' I Remote Bulwark Reconstruction", "Large 'Solace' I Remote Bulwark Reconstruction",
            "'Pandemonium' I Ballistic Enhancement", "Large 'Regard' I Power Projector", "Medium 'Regard' I Power Projector",
            "Heavy 'Knave' I Energy Drain", "Medium 'Knave' I Energy Drain", "'Stalwart' I Particle Field Magnifier",
            "'Copasetic' I Particle Field Acceleration", "Micro 'Vigor' I Core Augmentation", "EMP Generator Blueprint",
            "Hardwiring - Eifyr and Co 'Gunslinger' MX-2", "'Gloom' I White Noise ECM", "'Shade' I White Noise ECM", 
            "'Umbra' I White Noise ECM", "Armored Warfare Link - Damage Control", "Skirmish Warfare Link - Evasive Maneuvers", 
            "Siege Warfare Link - Active Shielding", "Information Warfare Link - Recon Operation", "Information Warfare Link - Electronic Superiority", 
            "Skirmish Warfare Link - Rapid Deployment", "Armored Warfare Link - Passive Defense", "Siege Warfare Link - Shield Harmonizing", 
            "Armored Warfare Link - Rapid Repair", "Siege Warfare Link - Shield Efficiency", "Armored Warfare Link - Damage Control Blueprint", 
            "Armored Warfare Link - Passive Defense Blueprint", "Armored Warfare Link - Rapid Repair Blueprint", 
            "Information Warfare Link - Electronic Superiority Blueprint", "Information Warfare Link - Recon Operation Blueprint", 
            "Information Warfare Link - Sensor Integrity Blueprint", "Siege Warfare Link - Shield Harmonizing Blueprint", 
            "Siege Warfare Link - Active Shielding Blueprint", "Siege Warfare Link - Shield Efficiency Blueprint", 
            "Skirmish Warfare Link - Evasive Maneuvers Blueprint", "Skirmish Warfare Link - Interdiction Maneuvers Blueprint", 
            "Skirmish Warfare Link - Rapid Deployment Blueprint", "Mining Foreman Link - Harvester Capacitor Efficiency", 
            "Mining Foreman Link - Harvester Capacitor Efficiency Blueprint", "Mining Foreman Link - Mining Laser Field Enhancement", 
            "Mining Foreman Link - Mining Laser Field Enhancement Blueprint", "Mining Foreman Link - Laser Optimization",
            "Mining Foreman Link - Laser Optimization Blueprint", "Drone Tracking Computer II", "Drone Tracking Computer II Blueprint",
            "Capital EMP Generator", "Capital EMP Generator Blueprint", "Station (Caldari 1 Wrecked)", "Minmatar Starbase Control Tower_LCO");
        $replace = array("Microorganisms", "Ice Microorganisms Extractor", "Barren Microorganisms Extractor", 
            "Temperate Microorganisms Extractor", "Oceanic Microorganisms Extractor", "Hardwiring - Eifyr and Co. 'Gunslinger' MX-0.5", 
            "Hardwiring - Eifyr and Co. 'Gunslinger' MX-1.5", "Hardwiring - Eifyr and Co. 'Gunslinger' MX-2.5", "Mechanics", 
            "Dual 1000mm 'Scout' Accelerator Cannon", "Quad 3500mm Gallium Cannon", "6x2500mm Heavy Gallium Repeating Cannon", 
            "Looking Glass Monocle Interface (right/gold)", "X5 Prototype Engine Enervator", "Looking Glass Monocle Interface (right/gray)", 
            "Looking Glass Monocle Interface (left/gold)", "Looking Glass Monocle Interface (left/gray)", 
            "Women's 'Structure' Skirt (camouflage)", "AGM Capacitor Charge Array", "Eutectic Capacitor Charge Array", 
            "Small Automated Carapace Restoration", "Medium Automated Carapace Restoration", "Large Automated Carapace Restoration", 
            "Micro F-4a Ld-Sulfate Capacitor Charge Unit", "Micro Peroxide Capacitor Power Cell", "Small F-4a Ld-Sulfate Capacitor Charge Unit", 
            "Small Peroxide Capacitor Power Cell", "Medium F-RX Prototype Capacitor Boost", "Large F-4a Ld-Sulfate Capacitor Charge Unit", 
            "Large Peroxide Capacitor Power Cell", "Micro F-RX Prototype Capacitor Boost", "Small F-RX Prototype Capacitor Boost", 
            "Heavy F-RX Prototype Capacitor Boost", "'Regard' Power Projector", "Small 'Knave' Energy Drain", "EP-S Gaussian Excavation Pulse", 
            "Connected Scanning CPU Uplink", "Linked Sensor Network", "Phase Switching Targeting Nexus", 
            "Quad LiF Fueled Booster Rockets", "LiF Fueled Booster Rockets", "Catalyzed Cold-Gas Arcjet Thrusters", 
            "Y-T8 Overcharged Hydrocarbon Microwarpdrive", "Monopropellant Hydrazine Boosters", "Cold-Gas Arcjet Thrusters", 
            "Y-S8 Hydrocarbon Afterburners", "Medium Peroxide Capacitor Power Cell", "Medium F-4a Ld-Sulfate Capacitor Charge Unit", 
            "Prototype Freight Sensors", "Prototype Sensor Booster", "Alumel-Wired Sensor Augmentation", "Reserve Gravimetric Scanners", 
            "Reserve LADAR Scanners", "Reserve Magnetometric Scanners", "Reserve Multi-Frequency Scanners", "Reserve RADAR Scanners", 
            "Fourier Transform Tracking Program", "Small Converse Deflection Catalyzer", "'Benefactor' Ward Reconstructor", 
            "75mm Prototype Gauss Gun", "75mm 'Scout' Accelerator Cannon", "150mm Prototype Gauss Gun", "150mm 'Scout' Accelerator Cannon", 
            "Dual 150mm Prototype Gauss Gun", "Dual 150mm 'Scout' Accelerator Cannon", "250mm Prototype Gauss Gun", 
            "250mm 'Scout' Accelerator Cannon", "Dual 250mm Prototype Gauss Gun", "Dual 250mm 'Scout' Accelerator Cannon", 
            "425mm Prototype Gauss Gun", "425mm 'Scout' Accelerator Cannon", "Prototype ECCM Radar Sensor Cluster", 
            "Prototype ECCM Ladar Sensor Cluster", "Prototype ECCM Gravimetric Sensor Cluster", "Prototype ECCM Omni Sensor Cluster", 
            "Prototype ECCM Magnetometric Sensor Cluster", "Small 'Atonement' Ward Projector", "Medium 'Atonement' Ward Projector", 
            "Micro 'Atonement' Ward Projector", "Large 'Atonement' Ward Projector", "125mm Light Gallium Machine Gun", 
            "125mm Light Prototype Automatic Cannon", "150mm Light Gallium Machine Gun", "150mm Light Prototype Automatic Cannon", 
            "200mm Light Gallium Machine Gun", "200mm Light Prototype Automatic Cannon", "250mm Light Gallium Cannon", 
            "250mm Light Prototype Siege Cannon", "Dual 180mm Gallium Machine Gun", "Dual 180mm Prototype Automatic Cannon", 
            "220mm Medium Gallium Machine Gun", "220mm Medium Prototype Automatic Cannon", "425mm Medium Gallium Machine Gun", 
            "425mm Medium Prototype Automatic Cannon", "650mm Medium Gallium Cannon", "650mm Medium Prototype Siege Cannon", 
            "Dual 425mm Gallium Machine Gun", "Dual 425mm Prototype Automatic Cannon", "Dual 650mm Gallium Repeating Cannon", 
            "Dual 650mm Prototype Repeating Siege Cannon", "800mm Heavy Gallium Repeating Cannon", "800mm Heavy Prototype Repeating Siege Cannon", 
            "1200mm Heavy Gallium Cannon", "1200mm Heavy Prototype Siege Cannon", "280mm Gallium Cannon", "280mm Prototype Siege Cannon", 
            "720mm Gallium Cannon", "720mm Prototype Siege Cannon", "1400mm Gallium Cannon", "1400mm Prototype Siege Cannon", 
            "'Penumbra' White Noise ECM", "'Anointed' EM Ward Reinforcement", "Large 'Vehemence' Shockwave Charge", 
            "Small 'Vehemence' Shockwave Charge", "Micro 'Vehemence' Shockwave Charge", "Medium 'Vehemence' Shockwave Charge", 
            "125mm 'Scout' Accelerator Cannon", "125mm Prototype Gauss Gun", "Medium Converse Deflection Catalyzer", 
            "Large Converse Deflection Catalyzer", "X-Large Converse Deflection Catalyzer", "Skirmish Warfare Link - Interdiction Maneuvers I", 
            "Information Warfare Link - Sensor Integrity I", "EM Pulse Generator", "200mm 'Scout' Accelerator Cannon", 
            "200mm Prototype Gauss Gun", "350mm 'Scout' Accelerator Cannon", "350mm Prototype Gauss Gun", "'Accord' Core Compensation", 
            "'Repose' Core Compensation", "Small 'Arup' Remote Bulwark Reconstruction", "Small 'Solace' Remote Bulwark Reconstruction", 
            "Medium 'Arup' Remote Bulwark Reconstruction", "Medium 'Solace' Remote Bulwark Reconstruction", 
            "Large 'Arup' Remote Bulwark Reconstruction", "Large 'Solace' Remote Bulwark Reconstruction", 
            "'Pandemonium' Ballistic Enhancement", "Large 'Regard' Power Projector", "Medium 'Regard' Power Projector", 
            "Heavy 'Knave' Energy Drain", "Medium 'Knave' Energy Drain", "'Stalwart' Particle Field Magnifier", 
            "'Copasetic' Particle Field Acceleration", "Micro 'Vigor' Core Augmentation", "EM Pulse Generator Blueprint", 
            "Hardwiring - Eifyr and Co. 'Gunslinger' MX-2", "'Gloom' White Noise ECM", "'Shade' White Noise ECM", "'Umbra' White Noise ECM", 
            "Armored Warfare Link - Damage Control I", "Skirmish Warfare Link - Evasive Maneuvers I", "Siege Warfare Link - Active Shielding I", 
            "Information Warfare Link - Recon Operation I", "Information Warfare Link - Electronic Superiority I", 
            "Skirmish Warfare Link - Rapid Deployment I", "Armored Warfare Link - Passive Defense I", 
            "Siege Warfare Link - Shield Harmonizing I", "Armored Warfare Link - Rapid Repair I", 
            "Siege Warfare Link - Shield Efficiency I", "Armored Warfare Link - Damage Control I Blueprint", 
            "Armored Warfare Link - Passive Defense I Blueprint", "Armored Warfare Link - Rapid Repair I Blueprint", 
            "Information Warfare Link - Electronic Superiority I Blueprint", "Information Warfare Link - Recon Operation I Blueprint", 
            "Information Warfare Link - Sensor Integrity I Blueprint", "Siege Warfare Link - Shield Harmonizing I Blueprint", 
            "Siege Warfare Link - Active Shielding I Blueprint", "Siege Warfare Link - Shield Efficiency I Blueprint", 
            "Skirmish Warfare Link - Evasive Maneuvers I Blueprint", "Skirmish Warfare Link - Interdiction Maneuvers I Blueprint", 
            "Skirmish Warfare Link - Rapid Deployment I Blueprint", "Mining Foreman Link - Harvester Capacitor Efficiency I", 
            "Mining Foreman Link - Harvester Capacitor Efficiency I Blueprint", "Mining Foreman Link - Mining Laser Field Enhancement I", 
            "Mining Foreman Link - Mining Laser Field Enhancement I Blueprint", "Mining Foreman Link - Laser Optimization I", 
            "Mining Foreman Link - Laser Optimization I Blueprint", "Omnidirectional Tracking Link II", "Omnidirectional Tracking Link II Blueprint", 
            "Capital EM Pulse Generator", "Capital EM Pulse Generator Blueprint", "Wrecked Caldari Station", "Indestructible Minmatar Starbase");
        $this->killmail_ = str_replace($search, $replace, $this->killmail_);
				// Reverse the MWD change from 'Monopropellant Hydrazine Boosters' above.
        $this->killmail_ = str_replace("Phased Monopropellant Hydrazine Boosters", "Phased Monopropellant I Hydrazine Boosters", $this->killmail_);
    }

        return $this->killmail_;
    }
}
