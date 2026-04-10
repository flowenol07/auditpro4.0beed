<?php
require_once('ai-responses.php');
?>
<style>
    /* Media query for screens 200px or larger */
    @media (min-width: 200px) {

        .container,
        .container-lg,
        .container-md,
        .container-sm,
        .container-xl {
            max-width: 100%;
            /* Set a max-width of 1250px for containers */
            margin: 0 auto;
            /* Center the container horizontally */
        }
    }

    body {
        font-family: Arial, sans-serif;
        background-color: #f5f5f5;
    }

    /* Full container holding both the PDF viewer and AI module */
    .container {
        display: flex;
        height: 100vh;
        /* Full screen height */
        width: 100vw;
        /* Full screen width */
        margin: 0;
        padding: 0;
        /* border: 1px solid #ccc; */
        /* Border around the entire container */
    }

    /* PDF viewer section */
    .pdf-viewer {
        flex: 1;
        overflow: hidden;
        padding-right: 20px;

    }

    /* AI module section */
    .ai-module {
        flex: 1;
        padding: 10px;
        overflow-y: auto;
        padding-left: 20px;

    }

    /* Style the buttons and form elements inside the AI module */
    .ai-module form {
        margin-bottom: 20px;
    }

    .ai-module button {
        margin-top: 5px;
        padding: 5px 10px;
        border: none;
        background-color: #007bff;
        color: white;
        cursor: pointer;
    }

    .ai-module button:hover {
        background-color: #0056b3;
    }


    textarea,
    input {
        margin-top: 10px;
        display: block;
        width: 100%;
        padding: 10px;
    }

    #responses {
        margin-top: 20px;
        background: #f8f9fa;
        padding: 10px;
        border-radius: 5px;
    }

    embed {
        width: 100%;
        height: 100%;
        border: none;
    }

    button {
        cursor: pointer;
        background: #007bff;
        color: white;
        border: none;

    }

    button:hover {
        background: #0056b3;
    }

    #user_prompt {
        width: 100%;
        min-height: 100px;
        padding: 10px;
        font-size: 16px;
        border: 1px solid #ccc;
        border-radius: 8px;
        resize: vertical;
        /* Allows resizing only in vertical direction */
        background-color: #f9f9f9;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    #user_prompt:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        outline: none;
    }

    #responses {
        max-height: 48800px;
        overflow-y: auto;
        border: 1px solid #ccc;
        padding: 10px;

    }

    #sidebar.active {
        left: -100% !important;
        /* Keeps the sidebar hidden */
    }

    #content.active {
        padding-top: 0 !important;
        padding-left: 0 !important;
        /* Prevents content shift */
    }

    .response {
        all: unset !important;
        /* Remove all inherited styles */
        display: block !important;
        /* Ensure it remains visible */
        width: 100%;
        /* Take full width */
    }

    /* Reset styles for all child elements inside .response */
    .response * {
        all: unset !important;
        /* Reset styles for all nested elements */
        display: block !important;
        /* Keep child elements visible */
        width: 100%;

    }

    .response .container,
    .response .container-lg,
    .response .container-md,
    .response .container-sm,
    .response .container-xl {
        max-width: 100%;
        /* Ensure full width */
        margin: 0 auto;
        /* Center the container */
    }

    @media (min-width: 1200px) {

        .container,
        .container-lg,
        .container-md,
        .container-sm,
        .container-xl {
            max-width: 100%;
        }
    }

    /* Reset styles for all nested elements inside .response */
    .response * {
        all: unset !important;
        /* Reset all styles */
        display: block !important;
        /* Ensure elements are visible */
        width: 100%;
    }

    /* Reset styles for .response itself */
    .response {
        all: unset !important;
        display: block !important;
        width: 100%;
    }

    /* Reset and style container elements within .response */
    .response .container,
    .response .container-lg,
    .response .container-md,
    .response .container-sm,
    .response .container-xl {
        all: unset !important;
        /* Reset styles */
        display: block !important;
        width: 100%;
        max-width: 100%;
        /* Ensure full width */
        margin: 0 auto;
        /* Center the container */
    }

    .card-header,
    .card-body,
    .card-title,
    .card-text {
        font-family: 'Mukta-Medium', sans-serif;
    }

    form button {
        background-color: #007bff;
        border: none;
        padding: 5px 20px;
        color: #fff;
        font-size: 16px;
        cursor: pointer;
        border-radius: 4px;
        margin-top:10px ;
        transition: background-color 0.3s ease;
    }

    form button:hover {
        background-color: #0056b3;
    }


    #loader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            color: #333;
            display: none; /* Hidden by default */
        }


</style>