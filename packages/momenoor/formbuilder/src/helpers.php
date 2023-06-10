<?php

if (! function_exists('mb_ucfirst')) {
    /**
     * Capitalize the first letter of a string,
     * even if that string is multibyte (non-latin alphabet).
     *
     * @param string $string  String to have its first letter capitalized.
     * @param false $encoding  Character encoding
     * @return string String with first letter capitalized.
     */
    function mb_ucfirst(string $string, false $encoding = false): string
    {
        $encoding = $encoding ? $encoding : mb_internal_encoding();

        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);

        return mb_strtoupper($firstChar, $encoding).$then;
    }
}
if (! function_exists('form')) {
    /**
     * Return a form field.
     *
     * @param string $name  Field name
     * @param array  $options  Options
     */
    function form(\Momenoor\FormBuilder\Form $form, array $options = [])
    {
        return $form->render($options);
    }
}
