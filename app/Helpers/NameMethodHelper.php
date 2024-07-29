<?php

namespace App\Helpers;


class NameMethodHelper
{

  private static $romanNumbers = [
    1000 => 'M',
    900 => 'CM',
    500 => 'D',
    400 => 'CD',
    100 => 'C',
    90 => 'XC',
    50 => 'L',
    40 => 'XL',
    10 => 'X',
    9 => 'IX',
    5 => 'V',
    4 => 'IV',
    1 => 'I'
  ];

  private static $alphabets = [
    'en' => [
      'A',
      'B',
      'C',
      'D',
      'E',
      'F',
      'G',
      'H',
      'I',
      'J',
      'K',
      'L',
      'M',
      'N',
      'O',
      'P',
      'Q',
      'R',
      'S',
      'T',
      'U',
      'V',
      'W',
      'X',
      'Y',
      'Z'
    ],
    'ar' => [
      'أ',
      'ب',
      'ت',
      'ث',
      'ج',
      'ح',
      'خ',
      'د',
      'ذ',
      'ر',
      'ز',
      'س',
      'ش',
      'ص',
      'ض',
      'ط',
      'ظ',
      'ع',
      'غ',
      'ف',
      'ق',
      'ك',
      'ل',
      'م',
      'ن',
      'ه',
      'و',
      'ي'
    ]
  ];

  public static function convertToRomanNumber(int $decimalNumber): string
  {

    $romanNumber = '';

    foreach (self::$romanNumbers as $value => $numeral) {
      while ($decimalNumber >= $value) {
        $romanNumber .= $numeral;
        $decimalNumber -= $value;
      }
    }

    return $romanNumber;
  }

  public static function generateAlphanumericNummering(int $count, string $language)
  {
    $selectedAlphabet = self::$alphabets[$language] ?? self::$alphabets['en'];
    return array_slice($selectedAlphabet, 0, $count);

  }
}
