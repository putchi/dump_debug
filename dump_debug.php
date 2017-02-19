<?php



/**
 * A function to var_dump given args (in a pretty way) and stop all processes at the end.
 *
 * @param mixed $expression
 * @param mixed $_          - optional [default = null]
 */
function ddd($expression, $_ = null) {
    $extraExpressions = array_slice(func_get_args(), 1);

    if (empty($extraExpressions)) {
        dump_debug($expression);
    } else {
        array_map(function($arg) {
            dump_debug($arg);
        }, func_get_args());
    }

    die;
}

function dump_debug($input, $collapse = false) {
    $recursive = function($data, $level=0) use (&$recursive, $collapse) {
        global $argv;

        $isTerminal = isset($argv);

        if (!$isTerminal && $level == 0 && !defined("DUMP_DEBUG_SCRIPT")) {
            define("DUMP_DEBUG_SCRIPT", true);

            echo '<meta charset="UTF-8">';
            echo '<script language="Javascript">function toggleDisplay(id) {';
            echo 'var state = document.getElementById("container"+id).style.display;';
            echo 'document.getElementById("container"+id).style.display = state == "inline" ? "none" : "inline";';
            echo 'document.getElementById("plus"+id).style.display = state == "inline" ? "inline" : "none";';
            echo '}</script>'."\n";
        }

        $type        = !is_string($data) && is_callable($data) ? "Callable" : ucfirst(gettype($data));
        $type_data   = null;
        $type_color  = null;
        $type_length = null;

        switch ($type) {
            case "String":
                $type_color  = "green";
                $type_length = strlen($data);
                $type_data   = "\"" . htmlentities($data) . "\""; break;

            case "Double":
            case "Float":
                $type        = "Float";
                $type_color  = "#0099c5";
                $type_length = strlen($data);
                $type_data   = htmlentities($data); break;

            case "Integer":
                $type_color  = "red";
                $type_length = strlen($data);
                $type_data   = htmlentities($data); break;

            case "Boolean":
                $type        = "Bool";
                $type_color  = "#92008d";
                $type_length = null;
                $type_data   = $data ? "TRUE" : "FALSE"; break;

            case "NULL":
                $type_length = null; break;

            case "Array":
                $type_length = count($data);
        }

        if (in_array($type, array("Object", "Array"))) {
            $notEmpty = false;

            foreach($data as $key => $value) {
                if (!$notEmpty) {
                    $notEmpty = true;

                    if ($isTerminal) {
                        echo $type . ($type_length !== null ? "(" . $type_length . ")" : "") . " " . ($type === "Array" ? "[" : "{");
                    } else {
                        $id = substr(md5(rand().":".$key.":".$level), 0, 8);

                        echo "<a href=\"javascript:toggleDisplay('". $id ."');\" style=\"text-decoration:none\">";
                        echo "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "")
                             . ($type === "Object" ? "&nbsp;{</span>" : "&nbsp;[</span>");
                        echo "</a>";
                        echo "<span id=\"plus". $id ."\" style=\"display: " . ($collapse ? "inline" : "none") . ";\">";
                        echo "<a href=\"javascript:toggleDisplay('". $id ."');\" style=\"text-decoration:none\">";
                        echo ($type === "Object" ? "<span style='color:#666666'>...}</span>" : "<span style='color:#666666'>...]</span>");
                        echo "</a></span>";
                        echo "<div id=\"container". $id ."\" style=\"display: " . ($collapse ? "" : "inline") . ";\">";
                    }

                    for ($i=0; $i <= $level; $i++) {
                        echo $isTerminal ? "    " : "<span style='color:black'></span>&nbsp;&nbsp;&nbsp;&nbsp;";
                    }

                    echo $isTerminal ? "\n" : "<br />";
                }

                for ($i=0; $i <= $level; $i++) {
                    echo $isTerminal ? "    " : "<span style='color:black'></span>&nbsp;&nbsp;&nbsp;&nbsp;";
                }

                echo $isTerminal ? "[" . $key . "] => " : "<span style='color:black'>[" . $key . "]&nbsp;=>&nbsp;</span>";

                call_user_func($recursive, $value, $level+1);
            }

            if ($notEmpty) {
                if ($level === 0 | $level > 1) {
                    $whitespace = $isTerminal ? "    " : "&nbsp;&nbsp;&nbsp;&nbsp;";
                    $indent     = str_repeat($whitespace, $level);
                    echo $isTerminal ? ($type === "Object" ? $indent . "}" : $indent . "]") :
                        ($type === "Object" ? "<span style='color:#666666'>" . $indent . "}</span>" : "<span style='color:#666666'>" . $indent . "]</span>");
                } else {
                    for ($i=0; $i < $level; $i++) {
                        echo $isTerminal ? ($type === "Object" ? "    }" : "    ]") :
                            ($type === "Object" ? "<span style='color:#666666'>&nbsp;&nbsp;&nbsp;&nbsp;}</span>" : "<span style='color:#666666'>&nbsp;&nbsp;&nbsp;&nbsp;]</span>");
                    }
                }

                if (!$isTerminal) {
                    echo "</div>";
                }

            } else {
                echo $isTerminal ?
                    $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " :
                    "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";
            }

        } else {
            echo $isTerminal ?
                $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "  " :
                "<span style='color:#666666'>" . $type . ($type_length !== null ? "(" . $type_length . ")" : "") . "</span>&nbsp;&nbsp;";

            if ($type_data != null) {
                echo $isTerminal ? $type_data : "<span style='color:" . $type_color . "'>" . $type_data . "</span>";
            }
        }

        echo $isTerminal ? "\n" : "<br />";
    };

    call_user_func($recursive, $input);
}
