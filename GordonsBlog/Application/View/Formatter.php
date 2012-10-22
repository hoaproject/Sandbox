<?php

namespace Application\View {

class Formatter {

    public static function html ( $value ) {

        return nl2br(htmlspecialchars($value));
    }
}

}
