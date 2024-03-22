
<div>
    {{-- In work, do what you enjoy. --}}
    <?php

    $output = shell_exec("Rscript test.R");

    echo "<pre>$output</pre>";

// Debugging output
echo "Output: $output<br>";
echo "File: " . __DIR__ . "\plot.png<br>";
echo "File exists: " . (file_exists(__DIR__ . "\plot.png") ? "Yes" : "No");


    ?>

    test
</div>
