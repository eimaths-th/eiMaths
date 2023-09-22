<?php
namespace App\Services;
use App\Interfaces\MoneyConvertServiceInterface;

class  MoneyConvertService implements MoneyConvertServiceInterface
{

    const BAHT_TEXT_NUMBERS = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
    const BAHT_TEXT_UNITS = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
    const BAHT_TEXT_ONE_IN_TENTH = 'เอ็ด';
    const BAHT_TEXT_TWENTY = 'ยี่';
    const BAHT_TEXT_INTEGER = 'ถ้วน';
    const BAHT_TEXT_BAHT = 'บาท';
    const BAHT_TEXT_SATANG = 'สตางค์';
    const BAHT_TEXT_POINT = 'จุด';
    // const sfielda = array('2' => 'GMEC', '4' => 'IMOCSEA', '5' => 'INFINITE Spelling Bee', '6' => 'GELOSEA', '7' => 'LIMOC', '8' => 'STEM', '9' => 'OLPCS');
    // const sfieldas = array('2' => 'G', '4' => 'J', '5' => 'I', '6' => 'L', '7' => 'M', '8' => 'N', '9' => 'O');
    const thmonth = array(
        "",
        "มกราคม",
        "กุมภาพันธ์",
        "มีนาคม",
        "เมษายน",
        "พฤษภาคม",
        "มิถุนายน",
        "กรกฎาคม",
        "สิงหาคม",
        "กันยายน",
        "ตุลาคม",
        "พฤศจิกายน",
        "ธันวาคม"
    );


    private function convertDigit($digit)
    {
        switch ($digit) {
            case '0':
                return 'zero';
            case '1':
                return 'one';
            case '2':
                return 'two';
            case '3':
                return 'three';
            case '4':
                return 'four';
            case '5':
                return 'five';
            case '6':
                return 'six';
            case '7':
                return 'seven';
            case '8':
                return 'eight';
            case '9':
                return 'nine';
        }
    } //convertDigit


    /**
     * convert group of number scale.
     *
     * @param integer $index
     * @return string
     */
    private function convertGroup($index)
    {
        switch ($index) {
            case 11:
                return ' decillion';
            case 10:
                return ' nonillion';
            case 9:
                return ' octillion';
            case 8:
                return ' septillion';
            case 7:
                return ' sextillion';
            case 6:
                return ' quintrillion';
            case 5:
                return ' quadrillion';
            case 4:
                return ' trillion';
            case 3:
                return ' billion';
            case 2:
                return ' million';
            case 1:
                return ' thousand';
            case 0:
                return '';
        }
    } // convertGroup

    /**
     * convert the number (and with dot).
     *
     * @param number $num number integer or decimal. negative or positive.
     * @return string translated number to text in Thai language.
     */
    public function convertNumber($num)
    {
        $num = strval($num);

        if (strpos($num, '.') !== false) {
            list($num, $dec) = explode('.', $num);
        } else {
            $dec = 0;
        }

        $output = '';

        if ($num[0] == '-') {
            $output = 'negative ';
            $num = ltrim($num, '-');
        } else if ($num[0] == '+') {
            $output = 'positive ';
            $num = ltrim($num, '+');
        }

        if ($num == '0') {
            $output .= 'zero';
        } else {
            if (strlen($num) > 36) {
                return 'Error!';
            }

            $num = str_pad($num, 36, '0', STR_PAD_LEFT);
            $group = rtrim(chunk_split($num, 3, ' '), ' ');
            $groups = explode(' ', $group);

            $groups2 = array();
            foreach ($groups as $g) {
                $groups2[] = $this->convertThreeDigit($g[0], $g[1], $g[2]);
            } // endforeach;

            for ($z = 0; $z < count($groups2); $z++) {
                if ($groups2[$z] != '') {
                    $output .= $groups2[$z] . $this->convertGroup(11 - $z) . ($z < 11 && !array_search('', array_slice($groups2, $z + 1, -1)) && $groups2[11] != '' && $groups[11][0] == '0' ? ' and ' : ', ');
                }
            } // endfor;

            $output = rtrim($output, ', ');
        } // endif;

        if ($dec > 0) {
            $output .= ' point';
            for ($i = 0; $i < strlen($dec); $i++) {
                $output .= ' ' . $this->convertDigit($dec[$i]);
            } // endfor;

        } // endif;
        else {
            $output .= ' Bath only ';
        }

        return $output;
    } // convertNumber


    /**
     * convert three digit.
     *
     * @param integer $dig1
     * @param integer $dig2
     * @param integer $dig3
     * @return string
     */
    private function convertThreeDigit($dig1, $dig2, $dig3)
    {
        $output = '';

        if ($dig1 == '0' && $dig2 == '0' && $dig3 == '0') {
            return '';
        }

        if ($dig1 != '0') {
            $output .= $this->convertDigit($dig1) . ' hundred';
            if ($dig2 != '0' || $dig3 != '0') {
                $output .= ' and ';
            }
        }

        if ($dig2 != '0') {
            $output .= $this->convertTwoDigit($dig2, $dig3);
        } else if ($dig3 != '0') {
            $output .= $this->convertDigit($dig3);
        }

        return $output;
    } // convertThreeDigit


    /**
     * convert two digits
     *
     * @param integer $dig1
     * @param integer $dig2
     * @return string
     */
    private function convertTwoDigit($dig1, $dig2)
    {
        if ($dig2 == '0') {
            switch ($dig1) {
                case '1':
                    return 'ten';
                case '2':
                    return 'twenty';
                case '3':
                    return 'thirty';
                case '4':
                    return 'forty';
                case '5':
                    return 'fifty';
                case '6':
                    return 'sixty';
                case '7':
                    return 'seventy';
                case '8':
                    return 'eighty';
                case '9':
                    return 'ninety';
            }
        } else if ($dig1 == '1') {
            switch ($dig2) {
                case '1':
                    return 'eleven';
                case '2':
                    return 'twelve';
                case '3':
                    return 'thirteen';
                case '4':
                    return 'fourteen';
                case '5':
                    return 'fifteen';
                case '6':
                    return 'sixteen';
                case '7':
                    return 'seventeen';
                case '8':
                    return 'eighteen';
                case '9':
                    return 'nineteen';
            }
        } else {
            $temp = $this->convertDigit($dig2);
            switch ($dig1) {
                case '2':
                    return "twenty-$temp";
                case '3':
                    return "thirty-$temp";
                case '4':
                    return "forty-$temp";
                case '5':
                    return "fifty-$temp";
                case '6':
                    return "sixty-$temp";
                case '7':
                    return "seventy-$temp";
                case '8':
                    return "eighty-$temp";
                case '9':
                    return "ninety-$temp";
            }
        }
    } // convertTwoDigit



    /**
     * Convert baht number to Thai text
     * @param double|int $number
     * @param bool $include_unit
     * @param bool $display_zero
     * @return string|null
     */
    public function baht_text($number, $include_unit = true, $display_zero = true)
    {

        if (!is_numeric($number)) {
            return null;
        }

        $log = floor(log($number, 10));
        if ($log > 5) {
            $millions = floor($log / 6);
            $million_value = pow(1000000, $millions);
            $normalised_million = floor($number / $million_value);
            $rest = $number - ($normalised_million * $million_value);
            $millions_text = '';
            for ($i = 0; $i < $millions; $i++) {
                $millions_text .= self::BAHT_TEXT_UNITS[6];
            }
            return $this($normalised_million, false) . $millions_text . $this($rest, true, false);
        }

        $number_str = (string)floor($number);
        $text = '';
        $unit = 0;

        if ($display_zero && $number_str == '0') {
            $text = self::BAHT_TEXT_NUMBERS[0];
        } else for ($i = strlen($number_str) - 1; $i > -1; $i--) {
            $current_number = (int)$number_str[$i];

            $unit_text = '';
            if ($unit == 0 && $i > 0) {
                $previous_number = isset($number_str[$i - 1]) ? (int)$number_str[$i - 1] : 0;
                if ($current_number == 1 && $previous_number > 0) {
                    $unit_text .= self::BAHT_TEXT_ONE_IN_TENTH;
                } else if ($current_number > 0) {
                    $unit_text .= self::BAHT_TEXT_NUMBERS[$current_number];
                }
            } else if ($unit == 1 && $current_number == 2) {
                $unit_text .= self::BAHT_TEXT_TWENTY;
            } else if ($current_number > 0 && ($unit != 1 || $current_number != 1)) {
                $unit_text .= self::BAHT_TEXT_NUMBERS[$current_number];
            }

            if ($current_number > 0) {
                $unit_text .= self::BAHT_TEXT_UNITS[$unit];
            }

            $text = $unit_text . $text;
            $unit++;
        }

        if ($include_unit) {
            $text .= self::BAHT_TEXT_BAHT;

            $moneyConvertService = new MoneyConvertService();
            $satang = explode('.', number_format($number, 2, '.', ''))[1];
            $text .= $satang == 0
                ? self::BAHT_TEXT_INTEGER
                : $moneyConvertService->baht_text($satang, false) . self::BAHT_TEXT_SATANG;
        } else {
            $exploded = explode('.', $number);
            if (isset($exploded[1])) {
                $text .= self::BAHT_TEXT_POINT;
                $decimal = (string)$exploded[1];
                for ($i = 0; $i < strlen($decimal); $i++) {
                    $text .= self::BAHT_TEXT_NUMBERS[$decimal[$i]];
                }
            }
        }

        return $text;
    }

}
