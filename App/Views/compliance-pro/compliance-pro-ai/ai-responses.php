<?php

require 'vendor/autoload.php';

use Smalot\PdfParser\Parser;

function extract_text_from_pdf($filePath)
{
    $parser = new Parser();
    $pdf = $parser->parseFile($filePath);
    return trim($pdf->getText()) ?: 'No text found in PDF.';
}

// Function to send queries to OpenAI API
function get_ai_response($documentText, $userPrompt)
{
    $url = "https://api.openai.com/v1/chat/completions";
    $headers = [
        "Authorization: Bearer " . OPENAI_API_KEY,
        "Content-Type: application/json"
    ];

    $prompt = "You are an AI assistant analyzing compliance documents which are RBI Circulars. Document:\n\n\"$documentText\"\n\nQuery: $userPrompt". 'in html markup for the table. The table should be styled with borders and alternating row  with all respose ';

    $data = json_encode([
        "model" => "gpt-4o-mini",
        "messages" => [["role" => "user", "content" => $prompt]],
        "max_tokens" => 4096,
        "temperature" => 0.3,  
        "top_p" => 1.0,        
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if (
        $http_code !== 200
    ) {
        return "Error: Unable to retrieve AI response. HTTP Code: $http_code.";
    }

    $result = json_decode(
        $response,
        true
    );
    return $result['choices'][0]['message']['content'] ?? 'No response from AI.';
}

// Function to download PDF from a URL
function download_pdf($fileUrl, $savePath)
{
    $ch = curl_init($fileUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $fileData = curl_exec($ch);
    curl_close($ch);

    if ($fileData) {
        file_put_contents($savePath, $fileData);
        return true;
    }
    return false;
}

// Handling File Upload or URL Parameter
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET["file"])) {
    $fileUrl = urldecode($_GET["file"]);

    if (filter_var($fileUrl, FILTER_VALIDATE_URL)) {
        $uploadDir = "uploads/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = basename(parse_url($fileUrl, PHP_URL_PATH));
        $filePath = $uploadDir . $fileName;

        if (download_pdf($fileUrl, $filePath)) {
            $_SESSION['pdf_text'] = extract_text_from_pdf($filePath);
            $_SESSION['pdf_name'] = $fileName;
            $_SESSION['pdf_path'] = $filePath;
        } else {
            die("Error: Failed to download the file.");
        }
    } else {
        die("Error: Invalid file URL.");
    }
}

// Handle File Upload
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["pdf_file"])) {
    if ($_FILES["pdf_file"]["error"] !== UPLOAD_ERR_OK) {
        die("Error: File upload failed.");
    }

    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', basename($_FILES["pdf_file"]["name"]));
    $filePath = $uploadDir . $fileName;

    if (mime_content_type($_FILES["pdf_file"]["tmp_name"]) !== 'application/pdf') {
        die("Error: Only PDF files are allowed.");
    }

    if (move_uploaded_file($_FILES["pdf_file"]["tmp_name"], $filePath)) {
        $_SESSION['pdf_text'] = extract_text_from_pdf($filePath);
        $_SESSION['pdf_name'] = $fileName;
        $_SESSION['pdf_path'] = $filePath;
    } else {
        die("Error: Failed to save the uploaded file.");
    }
}

// Handle AI Query Requests via AJAX
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["ajax_prompt"])) {
    if (!isset($_SESSION['pdf_text'])) {
        echo "Error: No document uploaded.";
        exit();
    }

    $userPrompt = trim($_POST["ajax_prompt"]);
    if (empty($userPrompt)) {
        echo "Error: Query cannot be empty.";
        exit();
    }

    echo get_ai_response($_SESSION['pdf_text'], $userPrompt);
    exit();
}

// Handle Session End
if (isset($_POST["end_session"])) {
    session_unset();
    session_destroy();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>

<body>


    <div class="card-header"><strong>Your AI Compliance Assistant!</strong></div>
    <div class="card-body">
        <p class="card-text">Get real-time insights and guidance on all information about you circular.</p>
    </div>

    <?php if (!isset($_SESSION['pdf_text'])): ?>
        <form action="" method="post" enctype="multipart/form-data">
            <input class="form-control" name="pdf_file" type="file" id="formFile" required accept="application/pdf">
            <button type="submit" class="btn btn-outline-secondary">Upload PDF</button>
        </form>
    <?php else: ?>

        <div class="container">
            <!-- PDF viewer -->
            <div class="pdf-viewer">
                <embed src="<?= htmlspecialchars($_SESSION['pdf_path']) ?>" type="application/pdf" width="100%" height="100%">
            </div>

            <!-- AI Module -->
            <div class="ai-module">
                <form id="queryForm">
                    <textarea class="form-control" name="user_prompt" id="user_prompt" rows="5" placeholder="Enter your compliance-related question"></textarea>

                    <button type="button" class="btn btn-success" onclick="askAI()">Ask AI</button>
                    <button type="button" class="btn btn-success" class="btn btn-success"onclick="clearResponses()">Clear Responses</button>
                    <button onclick="endSession()" class="btn btn-success">EXIT</button>
                </form>
            </div>
        </div>

        <div class="responses">
            <p id="responses"></p>

            <div id="loader">Loading...</div> 
        </div>

    <?php endif; ?>
    <script>
        function renderTable(tableData) {
            const table = document.createElement('table');
            table.style.borderCollapse = "collapse";
            table.style.width = "100%";
            table.style.marginTop = "10px";

            // Create header row
            const thead = document.createElement('thead');
            const headerRow = document.createElement('tr');
            tableData.headers.forEach(header => {
                const th = document.createElement('th');
                th.textContent = header;
                th.style.border = "1px solid #ccc";
                th.style.padding = "8px";
                th.style.backgroundColor = "#f2f2f2";
                headerRow.appendChild(th);
            });
            thead.appendChild(headerRow);
            table.appendChild(thead);

            // Create table body
            const tbody = document.createElement('tbody');
            tableData.rows.forEach(row => {
                const tr = document.createElement('tr');
                row.forEach(cell => {
                    const td = document.createElement('td');
                    td.textContent = cell;
                    td.style.border = "1px solid #ccc";
                    td.style.padding = "8px";
                    tr.appendChild(td);
                });
                tbody.appendChild(tr);
            });
            table.appendChild(tbody);
            return table;
        }

        // Parses pipe-delimited text data into an object with headers and rows
        function parseTabularText(textData) {
            const lines = textData.split("\n").filter(line => line.trim() !== "");
            let tableData = {
                headers: [],
                rows: []
            };

            // Identify the header line by finding the first line containing "**"
            // Otherwise, just use the first non-empty line
            let headerFound = false;
            lines.forEach((line, idx) => {
                // Check if line contains markdown-style header syntax (e.g. **Section**)
                if (!headerFound && line.indexOf("**") !== -1) {
                    const columns = line.split("|").map(col => col.trim()).filter(col => col !== "");
                    tableData.headers = columns;
                    headerFound = true;
                } else if (headerFound && line.indexOf("|") !== -1) {
                    const columns = line.split("|").map(col => col.trim()).filter(col => col !== "");
                    // Exclude lines that might be markdown separators
                    if (columns.join('').match(/^-+$/) || columns.length === 0) {
                        return;
                    }
                    tableData.rows.push(columns);
                }
            });
            return tableData;
        }

        function askAI() {
            const userPrompt = document.getElementById("user_prompt").value.trim();
            if (!userPrompt) {
                alert("Please enter a question.");
                return;
            }

            document.getElementById("loader").style.display = "flex";

            fetch("", { // Provide the correct endpoint URL if needed.
                    method: "POST",
                    body: new URLSearchParams({
                        ajax_prompt: userPrompt
                    })
                })
                .then(response => response.text())
                .then(data => {
                    console.log("Raw Response Data:", data);

                    // Create container for response
                    const responseContainer = document.createElement("div");
                    responseContainer.style.marginBottom = "20px";

                    // Append the question text
                    const questionPara = document.createElement("p");
                    questionPara.innerHTML = `<strong class="text-primary">Q:</strong> <span class="text-primary">${userPrompt}</span>`;
                    responseContainer.appendChild(questionPara);

                    // Try to parse the response as JSON
                    let jsonResponse;
                    try {
                        jsonResponse = JSON.parse(data);
                    } catch (e) {
                        jsonResponse = null;
                    }

                    if (jsonResponse && jsonResponse.headers && jsonResponse.rows) {

                        const table = renderTable(jsonResponse);
                        responseContainer.appendChild(table);
                    } else if (data.indexOf("|") !== -1) {

                        const tableData = parseTabularText(data);
                        if (tableData.headers.length > 0 && tableData.rows.length > 0) {
                            const table = renderTable(tableData);
                            responseContainer.appendChild(table);
                        } else {

                            const answerPara = document.createElement("p");
                            answerPara.innerHTML = `<strong>A:</strong> ${data}`;
                            responseContainer.appendChild(answerPara);
                        }
                    } else {

                        const answerPara = document.createElement("p");
                        answerPara.innerHTML = `<strong>A:</strong> ${data}`;
                        responseContainer.appendChild(answerPara);
                    }

                    document.getElementById("responses").appendChild(responseContainer);
                    responseContainer.scrollIntoView({ behavior: "smooth", block: "start" });

                })
                .catch(error => console.error("Error:", error)) .finally(() => {
                // Hide the loader after response is received
                document.getElementById("loader").style.display = "none";
            });
        }
    </script>