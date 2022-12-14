<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Session;

class CaesarCipherController extends BaseController
{
    public function index(): Factory|View|Application
    {
        if(Session::exists("data")) return view("caesarCipher")->with(["data" => Session::get("data")]);
        return view("caesarCipher");
    }

    public function compute(Request $request): Application|RedirectResponse|Redirector
    {
        $timerStart = microtime(true);
        $data = $request->all();
        $data["text"] = $this->normalize($data["text"]);

        if($data["action"] != "bruteforce"){
            $data["finalText"] = $this->performCaesar($data["text"],$data["shift"],$data["action"]);
            $data["shiftedAlphabet"] = $this->rotateAlphabet($request->input("shift"));
        }else{
            $data["bruteForceResult"] = $this->bruteForce($data["text"]);
            $data["shift"] = 0;
        }

        $time_elapsed_secs = microtime(true) - $timerStart;
        Session::flash("alert-info",trans("baseTexts.actionTook") . " ".$time_elapsed_secs . " s");
        Session::flash("data",$data);
        return redirect("caesarCipher");
    }

    function rotateAlphabet($key): array
    {
        $alphabet = [];
        foreach (range("A","Z") as $char){
            $alphabet[] = $char;
        }
        for ($i = 0; $i < $key; $i++) {
             $temp = array_shift($alphabet);
             $alphabet[] = $temp;
        }
        return $alphabet;
    }

    function bruteForce($textToDecrypt): array
    {
        $bruteForceResults = [];
        for($i = 0; $i < 26; $i++){
            $bruteForceResults[] = $this->performCaesar($textToDecrypt, $i, "decrypt");
        }
        return $bruteForceResults;
    }

    function normalize($string): string
    {
        $table = array(
            '??' => 'S', '??' => 's', '??' => 'Dj', '??' => 'dj', '??' => 'Z', '??' => 'z', '??' => 'C', '??' => 'c', '??' => 'C', '??' => 'c',
            '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'A', '??' => 'C', '??' => 'E', '??' => 'E',
            '??' => 'E', '??' => 'E', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'I', '??' => 'N', '??' => 'O', '??' => 'O', '??' => 'O',
            '??' => 'O', '??' => 'O', '??' => 'O', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'U', '??' => 'Y', '??' => 'B', '??' => 'Ss',
            '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'a', '??' => 'c', '??' => 'e', '??' => 'e',
            '??' => 'e', '??' => 'e', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'i', '??' => 'o', '??' => 'n', '??' => 'o', '??' => 'o',
            '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'o', '??' => 'u', '??' => 'u', '??' => 'u', '??' => 'y', '??' => 'e', '??' => 'b',
            '??' => 'y', '??' => 'R', '??' => 'r',
        );

        return strtr($string, $table);
    }

    function performCaesar($text, $s,$type): string
    {
        $result = "";

        if($type =="decrypt") $s = 26-$s;

        // traverse text
        for ($i = 0; $i < strlen($text); $i++)
        {
            if($text[$i] != " "){
            // apply transformation to each
            // character Encrypt Uppercase letters
            if (ctype_upper($text[$i]))
                $result = $result.chr((ord($text[$i]) +
                            $s - 65) % 26 + 65);

            // Encrypt Lowercase letters
            else
                $result = $result.chr((ord($text[$i]) +
                            $s - 97) % 26 + 97);
            }else{
                $result = $result.chr(32);
            }
        }

        // Return the resulting string
        return $result;
    }
}
