<?php


class TestGenerator {

    // Function to generate random data for an int
    public function generateInt($min = 0, $max = 100) {
        return rand($min, $max);
    }

    // Function to generate random data for a float
    public function generateFloat($min = 0, $max = 100, $precision = 2) {
        return round($min + mt_rand() / mt_getrandmax() * ($max - $min), $precision);
    }

    // Function to generate random data for a char
    public function generateChar() {
        return "'" . chr(rand(65, 122)) . "'";
    }

    // Function to generate random data for a bool
    public function generateBool() {
        return rand(0, 1) ? 'true' : 'false';
    }

    // Function to generate random data for a string
    public function generateString($minLength = 5, $maxLength = 15) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $length = rand($minLength, $maxLength); // Randomize the length of the string
        return '"' . substr(str_shuffle($characters), 0, $length) . '"';
    }


    // Function to generate random data for a vector or list of a given primitive type
    public function generateContainer($type, $length = 10, $min = 0, $max = 100) {
        $containerData = [];
        for ($i = 0; $i < $length; $i++) {
            $containerData[] = $this->{"generate$type"}($min, $max);
        }
        return '{' . implode(', ', $containerData) . '}';
    }
}

$testGenerator = new TestGenerator();