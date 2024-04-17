<?php
session_start();
require_once('header.php');
require_once('admin/db_connect.php');


// Retrieve the image path and canvas state from the query parameters
$imgPath = $_GET['img'] ?? '';

$canvasStateFromServer = $_POST['canvasState'] ?? ($_GET['canvasState'] ?? '');

$canvasStateFromDatabase = '';

if (isset($_GET['id'])) {
    $imageId = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT canvas_state FROM orders WHERE id = ?");
    $stmt->bind_param("i", $imageId);
    $stmt->execute();
    $stmt->bind_result($canvasStateFromDatabase);
    $stmt->fetch();
    $stmt->close();
}
// Store the image path in a session variable
$_SESSION['imgPath'] = 'assets/img/' . htmlspecialchars($imgPath, ENT_QUOTES, 'UTF-8');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Canvas Editor</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9f9f9;
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            height: 80vh;
            margin: 100;
            padding: 100px;
        }

        canvas {
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            margin-right: 500px;
        }


        button {
            background-color: #32CD32;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 8px 16px;
            margin: 5px 0;
            cursor: pointer;
            transition: background-color 0.3s, box-shadow 0.2s;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: block;
        }

        button:hover {
            background-color: #0056b3;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        #deleteText {
            background-color: #F535AA;
        }

        #deleteText:hover {
            background-color: #9E4638;
        }

        .controls {
            background: #fff;
            padding: 15px 20px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            border-radius: 5px;
            width: 300px;
            overflow-y: auto;
            /* Add scroll if content overflows */
            position: absolute;
            /* Change from fixed to absolute */
            top: 70px;
            right: 50px;
        }

        h3 {
            margin-bottom: 15px;
            font-size: 18px;
            color: #333;
        }


        .category {
            margin-bottom: 20px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            overflow: hidden;
        }

        .category-header {
            cursor: pointer;
            padding: 10px 15px;
            background-color: #f7f7f7;
            transition: background-color 0.3s;
            font-weight: 500;
        }

        .category-header:hover {
            background-color: #e0e0e0;
        }

        .category-content {
            display: none;
            padding: 10px 15px;
            background-color: #fff;
            border-top: 1px solid #e0e0e0;
        }

        .category-header.active {
            background-color: #e0e0e0;
        }


        #resizeContainer {
            display: flex;
            /* Align fields horizontally */
            justify-content: space-between;
            /* Space between fields */
            align-items: left;
            /* Align fields vertically */
        }

        .gg-arrow-left {
            box-sizing: border-box;
            position: relative;
            display: block;
            transform: scale(var(--ggs, 1));
            width: 30px;
            height: 30px
        }

        .gg-arrow-left::after,
        .gg-arrow-left::before {
            content: "";
            display: block;
            box-sizing: border-box;
            position: absolute;
            left: 3px
        }

        .gg-arrow-left::after {
            width: 8px;
            height: 8px;
            border-bottom: 2px solid;
            border-left: 2px solid;
            transform: rotate(45deg);
            bottom: 7px
        }

        .gg-arrow-left::before {
            width: 25px;
            height: 2px;
            bottom: 10px;
            background: currentColor
        }
    </style>
</head>

<body>

    <canvas id="canvas" width="500" height="400"></canvas>

    <div class="controls">
        <div class="category">
            <div class="upload-bg-image">
                <input type="file" id="bgImageUpload" accept="image/*">
                <button id="setBgImage">Set Background Image</button>
            </div>
            <div class="category">
                <h3 class="category-header active">Background Options:</h3>
                <div class="category-content">
                    <!-- Background Options Controls -->

                    Canvas Background Color: <input type="color" id="canvasBgColor" value="#ffffff">
                    <button id="applyCanvasBgColor">Apply Background Color</button>
                    <br>
                    Upload Background Image: <input type="file" id="bgImageUpload" accept="image/*">
                    <button id="setBgImage">Set Background Image</button>
                </div>
            </div>
            <h3 class="category-header">Text Options:</h3>
            <div class="category-content active-content" style="display: block;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <button id="addText">Add Text</button>
                    <button id="deleteText">Delete Text</button>
                    <br>
                </div>
                Font Size:
                <select id="fontSizeSelect">
                    <option value="12">12</option>
                    <option value="16">16</option>
                    <option value="20">20</option>
                    <option value="26">26</option>
                    <!-- Add more options as needed -->
                    <option value="custom">Custom</option>
                </select>
                <input type="number" id="fontSizeInput" value="16" min="1" step="1" style="display: none;">

                <br>
                <br>
                Font Family:
                <select id="fontFamily">
                    <option value="Arial">Arial</option>
                    <option value="Verdana">Verdana</option>
                    <option value="Times New Roman">Times New Roman</option>
                    <option value="Georgia">Georgia</option>
                    <option value="Tahoma">Tahoma</option>
                    <option value="Helvetica">Helvetica</option>
                    <option value="Courier New">Courier New</option>
                    <option value="Comic Sans MS">Comic Sans MS</option>
                    <option value="Impact">Impact</option>
                    <option value="Lucida Sans Unicode">Lucida Sans Unicode</option>
                    <option value="Trebuchet MS">Trebuchet MS</option>
                </select>

                <br>
                <br>
                Text Color: <input type="color" id="textColor" value="#000000">
                <br>
                <br>
                Background Color: <input type="color" id="bgColor" value="#ffffff">
                <button id="applyTextFormat">Apply Format</button>
                <br>

            </div>
        </div>



        <button id="saveAsImage">Save As Image</button>
        <button id="resizeCanvas">Resize Canvas</button>
        <div id="resizeContainer" style="display: none; margin-top: 20px;">
            Width: <input type="number" id="canvasWidth" value="800">
            Height: <input type="number" id="canvasHeight" value="400">
            <button id="applyResize">Apply Resize</button>
        </div>
    </div>

    <form id="canvasForm" action="canvas_editor.php" method="post">
        <input type="hidden" name="canvasState" id="canvasStateInput">
        <input type="hidden" name="id" value="<?php echo $imageId ?? ''; ?>">
    </form>

    <input type="hidden" name="id" value="<?php echo $imageId ?? ''; ?>">

    <a href="index.php" style="font-size: 30px; position: absolute; top: 10px; left: 10px;">
        <i class="gg-arrow-left"></i>
    </a>

</body>

</html>

<script>
    const canvas = new fabric.Canvas('canvas');

    // Structured Loading:
    const canvasStateFromDatabase = <?php echo json_encode($canvasStateFromDatabase); ?>;
    const canvasStateFromQuery = <?php echo json_encode($_GET['canvasState'] ?? ''); ?>;

    // First, try to load from the database
    if (canvasStateFromDatabase) {
        canvas.loadFromJSON(canvasStateFromDatabase, function() {
            canvas.renderAll();
            console.log('Canvas state loaded from database.');
        });
    }
    // If not in the database, try the query parameter
    else if (canvasStateFromQuery) {
        canvas.loadFromJSON(canvasStateFromQuery, function() {
            canvas.renderAll();
            console.log('Canvas state loaded from query parameter.');
        });
    }
    // If not in the query parameter, try session storage
    else {
        const savedCanvasState = sessionStorage.getItem('canvasState');
        if (savedCanvasState) {
            console.log('Loading canvas state from session storage...');
            canvas.loadFromJSON(savedCanvasState, function() {
                canvas.renderAll();
                console.log('Canvas state loaded from session storage.');
            });
            sessionStorage.removeItem('canvasState');
            console.log('Canvas state removed from session storage');
        }
    }

    const textObjects = []; // Array to store text objects

    // Create four resize handles for each corner
    const createResizeHandle = (left, top, originX, originY) => new fabric.Rect({
        width: 10,
        height: 10,
        fill: '#98AFC7',
        left: left,
        top: top,
        originX: originX,
        originY: originY,
        hasControls: false,
        selectable: true,
        rx: 20,
        ry: 20
    });

    const topLeft = createResizeHandle(0, 0, 'center', 'center');
    const topRight = createResizeHandle(canvas.width, 0, 'center', 'center');
    const bottomLeft = createResizeHandle(0, canvas.height, 'center', 'center');
    const bottomRight = createResizeHandle(canvas.width, canvas.height, 'center', 'center');

    canvas.add(topLeft, topRight, bottomLeft, bottomRight);

    let isResizing = false;
    let activeHandle = null;

    // Use the image path stored in the session
    // fabric.Image.fromURL('<?php echo $_SESSION['imgPath']; ?>', function(img) {
    //     const scaleX = canvas.width / img.width;
    //     const scaleY = canvas.height / img.height;
    //     const scale = Math.min(scaleX, scaleY);

    //     img.set({
    //         scaleX: scale,
    //         scaleY: scale,
    //         top: 0,
    //         left: 0
    //     });

    //     canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
    //         originX: 'left',
    //         originY: 'top',
    //         scaleX: scale,
    //         scaleY: scale
    //     });
    // });

    document.getElementById('addText').addEventListener('click', function() {
        const text = new fabric.IText('New Text', {
            left: 200,
            top: 200
        });
        canvas.add(text);
        textObjects.push(text); // Add the text object to the array
    });


    document.getElementById('deleteText').addEventListener('click', function() {
        canvas.remove(canvas.getActiveObject());
    });

    canvas.on('mouse:down', function(options) {
        if ([topLeft, topRight, bottomLeft, bottomRight].includes(options.target)) {
            activeHandle = options.target;
        }
    });

    canvas.on('mouse:move', function(options) {
        if (activeHandle) {
            const pointer = canvas.getPointer(options.e);
            switch (activeHandle) {
                case topLeft:
                    canvas.setWidth(canvas.width - (pointer.x - topLeft.left));
                    canvas.setHeight(canvas.height - (pointer.y - topLeft.top));
                    canvas.set({
                        left: pointer.x,
                        top: pointer.y
                    });
                    break;
                case topRight:
                    canvas.setWidth(pointer.x);
                    canvas.setHeight(canvas.height - (pointer.y - topRight.top));
                    canvas.set({
                        top: pointer.y
                    });
                    break;
                case bottomLeft:
                    canvas.setWidth(canvas.width - (pointer.x - bottomLeft.left));
                    canvas.setHeight(pointer.y);
                    canvas.set({
                        left: pointer.x
                    });
                    break;
                case bottomRight:
                    canvas.setWidth(pointer.x);
                    canvas.setHeight(pointer.y);
                    break;
            }
            canvas.renderAll();
        } else {
            const pointer = canvas.getPointer(options.e);
            if (pointer.x < 20 && pointer.y < 20) {
                canvas.hoverCursor = 'nwse-resize';
                topLeft.visible = true;
            } else if (pointer.x > canvas.width - 20 && pointer.y < 20) {
                canvas.hoverCursor = 'nesw-resize';
                topRight.visible = true;
            } else if (pointer.x < 20 && pointer.y > canvas.height - 20) {
                canvas.hoverCursor = 'nesw-resize';
                bottomLeft.visible = true;
            } else if (pointer.x > canvas.width - 20 && pointer.y > canvas.height - 20) {
                canvas.hoverCursor = 'nwse-resize';
                bottomRight.visible = true;
            } else {
                canvas.hoverCursor = 'default';
                [topLeft, topRight, bottomLeft, bottomRight].forEach(handle => handle.visible = false);
            }
            canvas.renderAll();
        }
    });

    canvas.on('mouse:up', function() {
        activeHandle = null;
    });

    canvas.on('after:render', function() {
        topLeft.set({
            left: 10,
            top: 10
        });
        topRight.set({
            left: canvas.width - 10,
            top: 10
        });
        bottomLeft.set({
            left: 10,
            top: canvas.height - 10
        });
        bottomRight.set({
            left: canvas.width - 10,
            top: canvas.height - 10
        });
    });
    document.getElementById('resizeCanvas').addEventListener('click', function() {
        // Show the input fields when the Resize button is clicked
        document.getElementById('resizeContainer').style.display = 'block';
    });

    document.getElementById('applyResize').addEventListener('click', function() {
        // Get the new width and height from the input fields
        const newWidth = document.getElementById('canvasWidth').value;
        const newHeight = document.getElementById('canvasHeight').value;

        // Resize the canvas
        canvas.setWidth(newWidth);
        canvas.setHeight(newHeight);
        canvas.renderAll();

        // Hide the input fields after resizing
        document.getElementById('resizeContainer').style.display = 'none';
    });
    // Event listener to handle font size selection
    document.getElementById('fontSizeSelect').addEventListener('change', function() {
        if (this.value === 'custom') {
            fontSizeInput.style.display = 'inline-block';
        } else {
            fontSizeInput.style.display = 'none';
            fontSizeInput.value = this.value; // Set the input value to match the selected option
        }
    });

    // Event listener for applying text format
    document.getElementById('applyTextFormat').addEventListener('click', function() {
        const activeText = canvas.getActiveObject();
        if (activeText && activeText.type === 'i-text') {
            // Use the value from fontSizeInput, which will be set correctly whether custom or not
            const fontSize = document.getElementById('fontSizeInput').value;
            const fontFamily = document.getElementById('fontFamily').value;
            const textColor = document.getElementById('textColor').value;
            const bgColor = document.getElementById('bgColor').value;

            // Convert bgColor from HEX to RGBA with 50% transparency
            const transparentBgColor = bgColor + '80'; // appending '80' makes it 50% transparent

            activeText.set({
                fontSize: parseInt(fontSize, 10),
                fontFamily: fontFamily,
                fill: textColor,
                backgroundColor: transparentBgColor
            });

            canvas.renderAll();
        }
    });


    // Event listener to change the canvas background color
    document.getElementById('applyCanvasBgColor').addEventListener('click', function() {
        const bgColor = document.getElementById('canvasBgColor').value;
        canvas.setBackgroundColor(bgColor, function() {
            canvas.renderAll();
        });
    });

    document.getElementById('setBgImage').addEventListener('click', function() {
        const inputElement = document.getElementById('bgImageUpload');
        const file = inputElement.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const imgObj = new Image();
                imgObj.src = event.target.result;
                imgObj.onload = function() {
                    const image = new fabric.Image(imgObj);
                    canvas.setBackgroundImage(image, canvas.renderAll.bind(canvas), {
                        scaleX: canvas.width / image.width,
                        scaleY: canvas.height / image.height
                    });
                }
            }
            reader.readAsDataURL(file);
        } else {
            alert('Please select an image first.');
        }
    });

    // Accordion functionality
    const headers = document.querySelectorAll('.category-header');
    headers.forEach(header => {
        header.addEventListener('click', function() {
            // Toggle display for the clicked category
            const content = this.nextElementSibling;
            content.style.display = content.style.display === 'none' ? 'block' : 'none';

            // Toggle active class for the clicked header
            this.classList.toggle('active');

            // Optionally: Collapse other categories
            headers.forEach(otherHeader => {
                if (otherHeader !== header) {
                    otherHeader.nextElementSibling.style.display = 'none';
                    otherHeader.classList.remove('active'); // Remove active class from other headers
                }
            });
        });
    });

    const fontSizeSelect = document.getElementById('fontSizeSelect');
    const fontSizeInput = document.getElementById('fontSizeInput');

    fontSizeSelect.addEventListener('change', function() {
        if (this.value === 'custom') {
            fontSizeInput.style.display = 'inline-block';
        } else {
            fontSizeInput.style.display = 'none';
            fontSizeInput.value = this.value; // Set the input value to match the selected option
        }
    });
    // Save canvas state to session storage whenever the canvas changes
    canvas.on('object:added object:modified object:removed', function() {
        sessionStorage.setItem('canvasState', JSON.stringify(canvas));
        console.log('Canvas state saved to session storage');
    });

    // Clear the canvas state from session storage when leaving the page
    window.addEventListener('beforeunload', function() {
        sessionStorage.removeItem('canvasState');
        console.log('Canvas state removed from session storage');
    });

    // Event listener to handle saving the canvas as an image
    document.getElementById('saveAsImage').addEventListener('click', function() {
        const editedImgData = canvas.toDataURL('png');
        const currentCanvasState = JSON.stringify(canvas); // Get the current canvas state

        const formData = new FormData();
        formData.append('editedImg', editedImgData);
        formData.append('canvasState', currentCanvasState); // Send the canvas state

        fetch('save_image.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Image saved successfully
                    alert('Image saved successfully!');
                    if (confirm("Do you want to proceed to send the card?")) {
                        // Save canvas state before redirecting to the Send Card page
                        const canvasStateJSON = JSON.stringify(canvas.toJSON());
                        window.location.href = 'sendCard.php?id=' + data.id;
                    }

                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error saving image:', error));
    });
</script>