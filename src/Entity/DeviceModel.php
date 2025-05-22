<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * DeviceModel
 */
#[ORM\Table(name: 'device_model')]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE', region: 'my_entity_region')]
#[ORM\Entity(repositoryClass: 'App\Repository\DeviceModelRepository')]
class DeviceModel extends BaseEntity
{
    public const MODELS = [
        DeviceVendor::VENDOR_TELTONIKA => [
            ['name' => self::TELTONIKA_FM3001],
            ['name' => self::TELTONIKA_FM36M1],
            ['name' => self::TELTONIKA_FMB920],
        ],
        DeviceVendor::VENDOR_ULBOTECH => [
            ['name' => self::ULBOTECH_T301],
        ],
        DeviceVendor::VENDOR_TOPFLYTECH => [
            ['name' => self::TOPFLYTECH_TLD1_A_E],
            ['name' => self::TOPFLYTECH_TLW1],
            ['name' => self::TOPFLYTECH_TLD2_L],
            ['name' => self::TOPFLYTECH_TLD1_DA_DE],
            ['name' => self::TOPFLYTECH_TLD2_DA_DE],
            ['name' => self::TOPFLYTECH_TLP1_LF, 'usage' => Device::USAGE_ASSET],
            ['name' => self::TOPFLYTECH_TLP1_SF, 'usage' => Device::USAGE_ASSET],
            ['name' => self::TOPFLYTECH_TLW2_12BL],
            ['name' => self::TOPFLYTECH_TLW1_4],
            ['name' => self::TOPFLYTECH_TLW1_8],
            ['name' => self::TOPFLYTECH_TLW1_10],
            ['name' => self::TOPFLYTECH_TLD1],
            ['name' => self::TOPFLYTECH_TLD1_D],
            ['name' => self::TOPFLYTECH_TLD2_D],
            ['name' => self::TOPFLYTECH_TLP1_LM],
            ['name' => self::TOPFLYTECH_TLP1_P],
            ['name' => self::TOPFLYTECH_TLP1_SM],
            ['name' => self::TOPFLYTECH_TLP2_SFB],
            ['name' => self::TOPFLYTECH_TLW2_2BL],
            ['name' => self::TOPFLYTECH_TLW2_12B],
            ['name' => self::TOPFLYTECH_PIONEERX_100],
            ['name' => self::TOPFLYTECH_PIONEERX_101],
        ],
        DeviceVendor::VENDOR_PIVOTEL => [
            ['name' => self::PIVOTEL_SPOT_TRACE1, 'usage' => Device::USAGE_SATELLITE],
            ['name' => self::PIVOTEL_9601, 'usage' => Device::USAGE_SATELLITE],
            ['name' => self::PIVOTEL_AMMT, 'usage' => Device::USAGE_SATELLITE],
        ],
        DeviceVendor::VENDOR_TRACCAR => [
            ['name' => self::TRACCAR_TELTONIKA_FM3001, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_TELTONIKA_FM36M1, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_TELTONIKA_FMB920, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_TELTONIKA_FMC130, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_TELTONIKA_FMM130, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_TTELTONIKA_FMC001, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_TELTONIKA_FMM001, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_ULBOTECH_T301, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_CONCOX_GV20, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_CONCOX_JM_VL04, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_CONCOX_JM_VL03, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_CONCOX_JM_VL02, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_CONCOX_CRX3, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_CONCOX_LINXIO_TR500, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_CONCOX_LINXIO_VX60, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_QUECLINK_GV500MAP, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_MEITRACK_P99G, 'parserType' => DeviceModel::PARSER_TRACCAR, 'usage' => Device::USAGE_PERSONAL],
            ['name' => self::TRACCAR_MEITRACK_P99L, 'parserType' => DeviceModel::PARSER_TRACCAR, 'usage' => Device::USAGE_PERSONAL],
            ['name' => self::TRACCAR_QUECLINK_GV300W, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_DIGITAL_MATTER_G100, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_DIGITAL_MATTER_G60, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_DIGITAL_MATTER_G52S, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_DIGITAL_MATTER_DART, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_DIGITAL_MATTER_OYSTER, 'parserType' => DeviceModel::PARSER_TRACCAR, 'usage' => Device::USAGE_ASSET],
            ['name' => self::TRACCAR_DIGITAL_MATTER_STING, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_DIGITAL_MATTER_REMORA, 'parserType' => DeviceModel::PARSER_TRACCAR, 'usage' => Device::USAGE_ASSET],
            ['name' => self::TRACCAR_EELINK_TK419, 'parserType' => DeviceModel::PARSER_TRACCAR],
            ['name' => self::TRACCAR_EELINK_TK419_4G, 'parserType' => DeviceModel::PARSER_TRACCAR],
        ],
        DeviceVendor::VENDOR_STREAMAX => [
            ['name' => self::STREAMAX_AD_PLUS_V2, 'parserType' => DeviceModel::PARSER_STREAMAX],
            ['name' => self::STREAMAX_AD_PLUS_V2_AD_C6SA, 'parserType' => DeviceModel::PARSER_STREAMAX],
        ],
    ];

    public const TELTONIKA_FM3001 = 'FM3001';
    public const TELTONIKA_FM36M1 = 'FM36M1';
    public const TELTONIKA_FMB920 = 'FMB920';
    public const TELTONIKA_FMC130 = 'FMC130';
    public const TELTONIKA_FMM130 = 'FMM130';
    public const TELTONIKA_FMC001 = 'FMC001';
    public const TELTONIKA_FMM001 = 'FMM001';

    public const ULBOTECH_T301 = 'T301';

    public const TOPFLYTECH_TLD1_A_E = 'TLD1-A-E';
    public const TOPFLYTECH_TLW1 = 'TLW1';
    public const TOPFLYTECH_TLD2_L = 'TLD2-L';
    public const TOPFLYTECH_TLD1_DA_DE = 'TLD1-DA-DE';
    public const TOPFLYTECH_TLD2_DA_DE = 'TLD2-DA-DE';
    public const TOPFLYTECH_TLP1_LF = 'TLP1-LF';
    public const TOPFLYTECH_TLP1_SF = 'TLP1-SF';
    public const TOPFLYTECH_TLW2_12BL = 'TLW2-12BL';
    public const TOPFLYTECH_TLW1_4 = 'TLW1-4';
    public const TOPFLYTECH_TLW1_8 = 'TLW1-8';
    public const TOPFLYTECH_TLW1_10 = 'TLW1-10';
    public const TOPFLYTECH_TLD1 = 'TLD1';
    public const TOPFLYTECH_TLD1_D = 'TLD1-D';
    public const TOPFLYTECH_TLD2_D = 'TLD2-D';
    public const TOPFLYTECH_TLP1_LM = 'TLP1-LM';
    public const TOPFLYTECH_TLP1_P = 'TLP1-P';
    public const TOPFLYTECH_TLP1_SM = 'TLP1-SM';
    public const TOPFLYTECH_TLP2_SFB = 'TLP2-SFB';
    public const TOPFLYTECH_TLW2_2BL = 'TLW2-2BL';
    public const TOPFLYTECH_TLW2_12B = 'TLW2-12B';
    public const TOPFLYTECH_PIONEERX_100 = 'PioneerX 100'; // TLW2-12BL
    public const TOPFLYTECH_PIONEERX_101 = 'PioneerX 101';

    public const PIVOTEL_SPOT_TRACE1 = 'STR1';
    public const PIVOTEL_9601 = '9601';
    public const PIVOTEL_AMMT = 'AMMT';
    public const PIVOTEL_SPOT_TRACE1_ALIAS = 'SPOT Trace';

    public const OYSTER_SIGFOX = 'Oyster (SigFox)';
    public const OYSTER_LORA = 'Oyster (LoRa)';

    public const TOPFLYTECH_TLD2_DA_DE_ALIAS = 'VX60-2-DA';
    public const TOPFLYTECH_TLD1_A_E_ALIAS = 'VX60-1-A';
    public const TOPFLYTECH_TLD2_L_ALIAS = 'VX60-2-L';
    public const TOPFLYTECH_TLW1_ALIAS = 'TR500-1-4';
    public const TOPFLYTECH_TLD1_DA_DE_ALIAS = 'VX60-1-DA';
    public const TOPFLYTECH_TLP1_LF_ALIAS = 'AT12';
    public const TOPFLYTECH_TLP1_SF_ALIAS = 'AT12S';
    public const TOPFLYTECH_TLW2_12BL_ALIAS = 'TR800-12';
    public const TOPFLYTECH_TLW1_4_ALIAS = 'TR500-1-4';
    public const TOPFLYTECH_TLW1_8_ALIAS = 'TR500-1-8';
    public const TOPFLYTECH_TLW1_10_ALIAS = 'TR500-1-10';
    public const TOPFLYTECH_TLD1_ALIAS = 'VX60-1';
    public const TOPFLYTECH_TLD1_D_ALIAS = 'VX60-1-D';
    public const TOPFLYTECH_TLD2_D_ALIAS = 'VX60-2-D';
    public const TOPFLYTECH_TLP1_LM_ALIAS = 'AT12-LM';
    public const TOPFLYTECH_TLP1_P_ALIAS = 'AT12-P';
    public const TOPFLYTECH_TLP1_SM_ALIAS = 'AT12-SM';
    public const TOPFLYTECH_TLP2_SFB_ALIAS = 'AT12-SFB';
    public const TOPFLYTECH_TLW2_2BL_ALIAS = 'TR500-2';
    public const TOPFLYTECH_TLW2_12B_ALIAS = 'PM500';

    public const CONCOX_GV20 = 'GV20';
    public const CONCOX_JM_VL02 = 'JM-VL02';
    public const CONCOX_JM_VL03 = 'JM-VL03';
    public const CONCOX_JM_VL04 = 'JM-VL04';
    public const CONCOX_CRX3 = 'CRX3';
    public const CONCOX_LINXIO_TR500 = 'TR500-lite-QS111'; // as CONCOX_CRX3
    public const CONCOX_LINXIO_VX60 = 'VX60-lite-QH302R'; // as CONCOX_CRX3
    public const MEITRACK_P99G = 'P99G';
    public const MEITRACK_P99L = 'P99L';
    public const QUECLINK_GV300W = 'GV300W';
    public const QUECLINK_GV500MAP = 'GV500MAP';
    public const DIGITAL_MATTER_G100 = 'G100';
    public const DIGITAL_MATTER_G60 = 'G60';
    public const DIGITAL_MATTER_G52S = 'G52S';
    public const DIGITAL_MATTER_DART = 'DART';
    public const DIGITAL_MATTER_OYSTER = 'OYSTER';
    public const DIGITAL_MATTER_STING = 'Sting';
    public const DIGITAL_MATTER_REMORA = 'REMORA';
    public const EELINK_TK419 = 'TK419';
    public const EELINK_TK419_4G = 'TK419-4G';

    public const TRACCAR_VENDOR_CONCOX = 'Concox';
    public const TRACCAR_VENDOR_MEITRACK = 'Meitrack';
    public const TRACCAR_VENDOR_QUECLINK = 'Queclink';
    public const TRACCAR_VENDOR_DIGITAL_MATTER = 'Digital Matter';
    public const TRACCAR_VENDOR_EELINK = 'EELink';
    public const TRACCAR_TELTONIKA_FM3001 = DeviceVendor::VENDOR_TELTONIKA . '-' . self::TELTONIKA_FM3001;
    public const TRACCAR_TELTONIKA_FM36M1 = DeviceVendor::VENDOR_TELTONIKA . '-' . self::TELTONIKA_FM36M1;
    public const TRACCAR_TELTONIKA_FMB920 = DeviceVendor::VENDOR_TELTONIKA . '-' . self::TELTONIKA_FMB920;
    public const TRACCAR_TELTONIKA_FMC130 = DeviceVendor::VENDOR_TELTONIKA . '-' . self::TELTONIKA_FMC130;
    public const TRACCAR_TELTONIKA_FMM130 = DeviceVendor::VENDOR_TELTONIKA . '-' . self::TELTONIKA_FMM130;
    public const TRACCAR_TTELTONIKA_FMC001 = DeviceVendor::VENDOR_TELTONIKA . '-' . self::TELTONIKA_FMC001;
    public const TRACCAR_TELTONIKA_FMM001 = DeviceVendor::VENDOR_TELTONIKA . '-' . self::TELTONIKA_FMM001;
    public const TRACCAR_ULBOTECH_T301 = DeviceVendor::VENDOR_ULBOTECH . '-' . self::ULBOTECH_T301;
    public const TRACCAR_CONCOX_GV20 = self::TRACCAR_VENDOR_CONCOX . '-' . self::CONCOX_GV20;
    public const TRACCAR_CONCOX_JM_VL02 = self::TRACCAR_VENDOR_CONCOX . '-' . self::CONCOX_JM_VL02;
    public const TRACCAR_CONCOX_JM_VL03 = self::TRACCAR_VENDOR_CONCOX . '-' . self::CONCOX_JM_VL03;
    public const TRACCAR_CONCOX_JM_VL04 = self::TRACCAR_VENDOR_CONCOX . '-' . self::CONCOX_JM_VL04;
    public const TRACCAR_CONCOX_CRX3 = self::TRACCAR_VENDOR_CONCOX . '-' . self::CONCOX_CRX3;
    public const TRACCAR_CONCOX_LINXIO_TR500 = self::TRACCAR_VENDOR_CONCOX . '-' . self::CONCOX_LINXIO_TR500;
    public const TRACCAR_CONCOX_LINXIO_TR500_ALIAS = DeviceVendor::TOPFLYTECH_ALIAS . '-' . self::CONCOX_LINXIO_TR500;
    public const TRACCAR_CONCOX_LINXIO_VX60 = self::TRACCAR_VENDOR_CONCOX . '-' . self::CONCOX_LINXIO_VX60;
    public const TRACCAR_CONCOX_LINXIO_VX60_ALIAS = DeviceVendor::TOPFLYTECH_ALIAS . '-' . self::CONCOX_LINXIO_VX60;
    public const TRACCAR_MEITRACK_P99G = self::TRACCAR_VENDOR_MEITRACK . '-' . self::MEITRACK_P99G;
    public const TRACCAR_MEITRACK_P99L = self::TRACCAR_VENDOR_MEITRACK . '-' . self::MEITRACK_P99L;
    public const TRACCAR_QUECLINK_GV300W = self::TRACCAR_VENDOR_QUECLINK . '-' . self::QUECLINK_GV300W;
    public const TRACCAR_QUECLINK_GV500MAP = self::TRACCAR_VENDOR_QUECLINK . '-' . self::QUECLINK_GV500MAP;
    public const TRACCAR_DIGITAL_MATTER_G100 = self::TRACCAR_VENDOR_DIGITAL_MATTER . '-' . self::DIGITAL_MATTER_G100;
    public const TRACCAR_DIGITAL_MATTER_G60 = self::TRACCAR_VENDOR_DIGITAL_MATTER . '-' . self::DIGITAL_MATTER_G60;
    public const TRACCAR_DIGITAL_MATTER_G52S = self::TRACCAR_VENDOR_DIGITAL_MATTER . '-' . self::DIGITAL_MATTER_G52S;
    public const TRACCAR_DIGITAL_MATTER_DART = self::TRACCAR_VENDOR_DIGITAL_MATTER . '-' . self::DIGITAL_MATTER_DART;
    public const TRACCAR_DIGITAL_MATTER_OYSTER = self::TRACCAR_VENDOR_DIGITAL_MATTER . '-' . self::DIGITAL_MATTER_OYSTER;
    public const TRACCAR_DIGITAL_MATTER_STING = self::TRACCAR_VENDOR_DIGITAL_MATTER . '-' . self::DIGITAL_MATTER_STING;
    public const TRACCAR_DIGITAL_MATTER_REMORA = self::TRACCAR_VENDOR_DIGITAL_MATTER . '-' . self::DIGITAL_MATTER_REMORA;
    public const TRACCAR_EELINK_TK419 = self::TRACCAR_VENDOR_EELINK . '-' . self::EELINK_TK419;
    public const TRACCAR_EELINK_TK419_4G = self::TRACCAR_VENDOR_EELINK . '-' . self::EELINK_TK419_4G;

    public const STREAMAX_AD_PLUS_V2 = 'AD_PLUS_V2/AD_LITE';
    public const STREAMAX_AD_PLUS_V2_AD_C6SA = 'AD_PLUS_V2/AD_C6SA';

    public const PARSER_CUSTOM = 1;
    public const PARSER_TRACCAR = 2;
    public const PARSER_STREAMAX = 3;

    public const MODEL_ALIAS = [
        self::TELTONIKA_FM3001 => self::TELTONIKA_FM3001,
        self::TELTONIKA_FM36M1 => self::TELTONIKA_FM36M1,
        self::TELTONIKA_FMB920 => self::TELTONIKA_FMB920,

        self::ULBOTECH_T301 => self::ULBOTECH_T301,

        self::TOPFLYTECH_TLD1_A_E => self::TOPFLYTECH_TLD1_A_E_ALIAS,
        self::TOPFLYTECH_TLW1 => self::TOPFLYTECH_TLW1_ALIAS,
        self::TOPFLYTECH_TLD2_L => self::TOPFLYTECH_TLD2_L_ALIAS,
        self::TOPFLYTECH_TLD1_DA_DE => self::TOPFLYTECH_TLD1_DA_DE_ALIAS,
        self::TOPFLYTECH_TLD2_DA_DE => self::TOPFLYTECH_TLD2_DA_DE_ALIAS,
        self::TOPFLYTECH_TLP1_LF => self::TOPFLYTECH_TLP1_LF_ALIAS,
        self::TOPFLYTECH_TLP1_SF => self::TOPFLYTECH_TLP1_SF_ALIAS,
        self::TOPFLYTECH_TLW2_12BL => self::TOPFLYTECH_TLW2_12BL_ALIAS,
        self::TOPFLYTECH_TLW1_4 => self::TOPFLYTECH_TLW1_4_ALIAS,
        self::TOPFLYTECH_TLW1_8 => self::TOPFLYTECH_TLW1_8_ALIAS,
        self::TOPFLYTECH_TLW1_10 => self::TOPFLYTECH_TLW1_10_ALIAS,
        self::TOPFLYTECH_TLD1 => self::TOPFLYTECH_TLD1_ALIAS,
        self::TOPFLYTECH_TLD1_D => self::TOPFLYTECH_TLD1_D_ALIAS,
        self::TOPFLYTECH_TLD2_D => self::TOPFLYTECH_TLD2_D_ALIAS,
        self::TOPFLYTECH_TLP1_LM => self::TOPFLYTECH_TLP1_LM_ALIAS,
        self::TOPFLYTECH_TLP1_P => self::TOPFLYTECH_TLP1_P_ALIAS,
        self::TOPFLYTECH_TLP1_SM => self::TOPFLYTECH_TLP1_SM_ALIAS,
        self::TOPFLYTECH_TLP2_SFB => self::TOPFLYTECH_TLP2_SFB_ALIAS,
        self::TOPFLYTECH_TLW2_2BL => self::TOPFLYTECH_TLW2_2BL_ALIAS,
        self::TOPFLYTECH_TLW2_12B => self::TOPFLYTECH_TLW2_12B_ALIAS,
        self::TOPFLYTECH_PIONEERX_100 => self::TOPFLYTECH_PIONEERX_100,
        self::TOPFLYTECH_PIONEERX_101 => self::TOPFLYTECH_PIONEERX_101,

        self::PIVOTEL_SPOT_TRACE1 => self::PIVOTEL_SPOT_TRACE1_ALIAS,
        self::PIVOTEL_9601 => self::PIVOTEL_9601,
        self::PIVOTEL_AMMT => self::PIVOTEL_AMMT,

        self::OYSTER_SIGFOX => self::OYSTER_SIGFOX,
        self::OYSTER_LORA => self::OYSTER_LORA,

        self::TRACCAR_TELTONIKA_FM3001 => self::TRACCAR_TELTONIKA_FM3001,
        self::TRACCAR_TELTONIKA_FM36M1 => self::TRACCAR_TELTONIKA_FM36M1,
        self::TRACCAR_TELTONIKA_FMB920 => self::TRACCAR_TELTONIKA_FMB920,
        self::TRACCAR_TELTONIKA_FMC130 => self::TRACCAR_TELTONIKA_FMC130,
        self::TRACCAR_TELTONIKA_FMM130 => self::TRACCAR_TELTONIKA_FMM130,
        self::TRACCAR_TTELTONIKA_FMC001 => self::TRACCAR_TTELTONIKA_FMC001,
        self::TRACCAR_TELTONIKA_FMM001 => self::TRACCAR_TELTONIKA_FMM001,
        self::TRACCAR_ULBOTECH_T301 => self::TRACCAR_ULBOTECH_T301,
        self::TRACCAR_CONCOX_GV20 => self::TRACCAR_CONCOX_GV20,
        self::TRACCAR_CONCOX_JM_VL02 => self::TRACCAR_CONCOX_JM_VL02,
        self::TRACCAR_CONCOX_JM_VL03 => self::TRACCAR_CONCOX_JM_VL03,
        self::TRACCAR_CONCOX_JM_VL04 => self::TRACCAR_CONCOX_JM_VL04,
        self::TRACCAR_CONCOX_CRX3 => self::TRACCAR_CONCOX_CRX3,
        self::TRACCAR_CONCOX_LINXIO_TR500 => self::TRACCAR_CONCOX_LINXIO_TR500_ALIAS,
        self::TRACCAR_CONCOX_LINXIO_VX60 => self::TRACCAR_CONCOX_LINXIO_VX60_ALIAS,
        self::TRACCAR_MEITRACK_P99G => self::TRACCAR_MEITRACK_P99G,
        self::TRACCAR_MEITRACK_P99L => self::TRACCAR_MEITRACK_P99L,
        self::TRACCAR_QUECLINK_GV300W => self::TRACCAR_QUECLINK_GV300W,
        self::TRACCAR_QUECLINK_GV500MAP => self::TRACCAR_QUECLINK_GV500MAP,
        self::TRACCAR_DIGITAL_MATTER_G100 => self::TRACCAR_DIGITAL_MATTER_G100,
        self::TRACCAR_DIGITAL_MATTER_G60 => self::TRACCAR_DIGITAL_MATTER_G60,
        self::TRACCAR_DIGITAL_MATTER_G52S => self::TRACCAR_DIGITAL_MATTER_G52S,
        self::TRACCAR_DIGITAL_MATTER_DART => self::TRACCAR_DIGITAL_MATTER_DART,
        self::TRACCAR_DIGITAL_MATTER_OYSTER => self::TRACCAR_DIGITAL_MATTER_OYSTER,
        self::TRACCAR_DIGITAL_MATTER_STING => self::TRACCAR_DIGITAL_MATTER_STING,
        self::TRACCAR_DIGITAL_MATTER_REMORA => self::TRACCAR_DIGITAL_MATTER_REMORA,
        self::TRACCAR_EELINK_TK419 => self::TRACCAR_EELINK_TK419,
        self::TRACCAR_EELINK_TK419_4G => self::TRACCAR_EELINK_TK419_4G,
        self::STREAMAX_AD_PLUS_V2 => self::STREAMAX_AD_PLUS_V2,
        self::STREAMAX_AD_PLUS_V2_AD_C6SA => self::STREAMAX_AD_PLUS_V2_AD_C6SA,
    ];

    public const DEFAULT_DISPLAY_VALUES = [
        'protocol'
    ];

    public function __construct(array $fields)
    {
        $this->vendor = $fields['vendor'];
        $this->name = $fields['name'];
        $this->protocol = $fields['protocol'] ?? null;
        $this->alias = $fields['alias'] ?? null;
        $this->parserType = $fields['parserType'] ?? self::PARSER_CUSTOM;
        $this->usage = $fields['usage'] ?? Device::USAGE_VEHICLE;
    }

    public function toArray(array $include = [], ?User $user = null): array
    {
        $data = [];
        $data['id'] = $this->id;

        if (empty($include)) {
            $include = self::DEFAULT_DISPLAY_VALUES;
        }
        if (in_array('name', $include, true)) {
            $data['name'] = ($user && !$user->isInAdminTeam()) ? $this->getAlias() : $this->getName();
        }
        if (in_array('protocol', $include, true)) {
            $data['protocol'] = $this->getProtocol();
        }
        if (in_array('alias', $include, true)) {
            $data['alias'] = $this->getAlias();
        }
        if (in_array('parserType', $include, true)) {
            $data['parserType'] = $this->getParserType();
        }
        if (in_array('parserType', $include, true)) {
            $data['parserType'] = $this->getParserType();
        }
        if (in_array('usage', $include, true)) {
            $data['usage'] = $this->getUsage();
        }

        return $data;
    }

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private $id;

    /**
     * @var int
     */
    #[ORM\ManyToOne(targetEntity: 'DeviceVendor', inversedBy: 'models', fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'vendor_id', referencedColumnName: 'id')]
    private $vendor;

    /**
     * @var string
     */
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    private $name;

    /**
     * @var string|null
     */
    #[ORM\Column(name: 'protocol', type: 'string', length: 255, nullable: true)]
    private $protocol;

    /**
     * @var string
     */
    #[ORM\Column(name: 'alias', type: 'string', length: 255, nullable: true)]
    private $alias;

    /**
     * @var int
     */
    #[ORM\Column(name: 'parser_type', type: 'smallint', length: 1, options: ['default' => '1'])]
    private $parserType = self::PARSER_CUSTOM;

    /**
     * @var string
     */
    #[ORM\Column(name: 'usage', type: 'string', length: 255, nullable: true)]
    private $usage;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set vendor.
     *
     * @param DeviceVendor $vendor
     *
     * @return DeviceModel
     */
    public function setVendor(DeviceVendor $vendor)
    {
        $this->vendor = $vendor;

        return $this;
    }

    /**
     * Get vendor.
     *
     * @return DeviceVendor
     */
    public function getVendor(): DeviceVendor
    {
        return $this->vendor;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return DeviceModel
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set protocol.
     *
     * @param string|null $protocol
     *
     * @return DeviceModel
     */
    public function setProtocol($protocol = null)
    {
        $this->protocol = $protocol;

        return $this;
    }

    /**
     * Get protocol.
     *
     * @return string|null
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    public function setAlias($alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    /**
     * @return int
     */
    public function getParserType(): int
    {
        return $this->parserType;
    }

    /**
     * @param int $parserType
     * @return self
     */
    public function setParserType(int $parserType): self
    {
        $this->parserType = $parserType;

        return $this;
    }

    /**
     * @return string
     */
    public function getVendorName(): string
    {
        return $this->getVendor()->getName();
    }

    /**
     * @return array
     */
    public static function getAvailableParserTypes(): array
    {
        return [
            self::PARSER_CUSTOM,
            self::PARSER_TRACCAR,
            self::PARSER_STREAMAX,
        ];
    }

    /**
     * @return array
     */
    public static function getParserTypes(): array
    {
        return [
            [
                'id' => self::PARSER_CUSTOM,
                'name' => 'custom',
                'label' => 'Custom',
            ],
            [
                'id' => self::PARSER_TRACCAR,
                'name' => 'traccar',
                'label' => 'Traccar',
            ],
            [
                'id' => self::PARSER_STREAMAX,
                'name' => 'streamax',
                'label' => 'Streamax',
            ],
        ];
    }

    public function getUsage(): ?string
    {
        return $this->usage;
    }

    public function setUsage(string $usage): self
    {
        $this->usage = $usage;

        return $this;
    }
}
