<?php


session_start();
require_once('header.php');
require_once('admin/db_connect.php');

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$canvasStatePost = filter_input(INPUT_POST, 'canvasState');
$canvasStateGet = filter_input(INPUT_GET, 'canvasState');

if ($canvasStatePost !== null) {
    $canvasState = htmlspecialchars($canvasStatePost, ENT_QUOTES, 'UTF-8');
} else if ($canvasStateGet !== null) {
    $canvasState = htmlspecialchars($canvasStateGet, ENT_QUOTES, 'UTF-8');
} else {
    $canvasState = null;
}

if ($id !== null) {
    $stmt = $conn->prepare("SELECT edit_image, canvas_state FROM edit_card WHERE id = ?");
    if ($stmt === false) {
        die("Failed to prepare statement: " . $conn->error);
    }
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($edit_image, $canvasState);
    $stmt->fetch();
    $stmt->close();
} else {
    echo "Invalid image ID.";
    exit;
}

// Construct the correct path to the edited image

$editedImageURL = "assets/img/edited_images/" . basename($edit_image);

if (!isset($_SESSION['imgPath'])) {
    $_SESSION['imgPath'] = 'assets/img/image.jpg';
} else {
    // Validate the session data here
}
$imgPath = $_SESSION['imgPath'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = $admin->save_order();
    if ($result === 1) {
        echo 1; // Echo '1' to indicate success to your AJAX call
        exit;
    } else {
        // You could log the error message here or even return it to AJAX for debugging
        echo $result; // Echoing the error to the AJAX response for debugging
        exit;
    }
}


?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Card</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/4.5.0/fabric.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <!-- <div class="containerSend">
        <hr class="divider my-4 bg-dark" />
    </div> -->
    <br><br><br>
    <div class="controlSend">
        <div class="containerSend">
            <!-- User information form -->
            <div class="form-container">
                <h2>Enter Your Information:</h2>
                <form action="sendCard.php" method="POST" id="checkout-frm">
                    <!-- Display the saved image using the provided image path -->
                    <img class="saved-image" src="<?php echo $editedImageURL; ?>" alt="Saved Image">

                    <div class="form-group">
                        <label for="" class="control-label">Your Name</label>
                        <input type="text" name="name" required="" class="form-control" value="<?php echo isset($_SESSION['login_first_name']) ? $_SESSION['login_first_name'] : ''; ?> <?php echo isset($_SESSION['login_last_name']) ? $_SESSION['login_last_name'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="" class="control-label">From</label>
                        <input type="email" name="email" required="" class="form-control" value="<?php echo isset($_SESSION['login_email']) ? $_SESSION['login_email'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">To</label>
                        <input type="email" name="to_email" required="" class="form-control" value="<?php echo isset($_POST['to_email']) ? $_POST['to_email'] : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="" class="control-label">Message</label>
                        <textarea name="message" class="form-control" rows="4" required=""></textarea>
                    </div>
                    <div class="form-group">
                        <label for="delivery_option" class="control-label">Delivery Option</label>
                        <select name="delivery_option" id="delivery_option" class="form-control" required>
                            <option value="immediate">Send Immediately</option>
                            <option value="scheduled">Schedule for Later</option>
                        </select>
                    </div>

                    <div class="form-group" id="schedule_datetime_wrapper" style="display: none;">
                        <label for="schedule_datetime" class="control-label">Schedule Date and Time</label>
                        <input type="datetime-local" name="schedule_datetime" id="schedule_datetime" class="form-control">
                    </div>
                    <div class="text-center">
                        <button id="send-button" class="btn btn-block btn-outline-dark">Send</button>
                    </div>


                    <input type="hidden" name="canvasState" value="<?php echo htmlspecialchars($canvasState, ENT_QUOTES, 'UTF-8'); ?>">


                </form>
            </div>


            <!-- Add this line to your HTML file -->
            <div id="loading" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10000;">
                <img src="assets/img/loading/FadingCircles.gif" alt="Loading...">

            </div>



            <?php if (isset($successMessage)) : ?>
                <p style="color: green;"><?php echo $successMessage; ?></p>
            <?php endif; ?>

            <!-- Optional: Display an error message if the email was not sent -->
            <?php if (isset($errorMessage) && !isset($successMessage)) : ?>
                <p style="color: red;"><?php echo $errorMessage; ?></p>
            <?php endif; ?>
        </div>
    </div>
    <!-- Add a back button to go back to the previous edit session -->
    <a class="back-link top-left" href="javascript:history.back();">Go Back to Editor</a>


    <style>
        #uni_modal .modal-footer {
            display: none;
        }
    </style>

    <script>
        $(document).ready(function() {
            $('#delivery_option').change(function() {
                var selectedOption = $(this).val();
                if (selectedOption === 'scheduled') {
                    $('#schedule_datetime_wrapper').show();
                } else {
                    $('#schedule_datetime_wrapper').hide();
                }
            });

            $('#send-button').click(function(event) {
                event.preventDefault();

                var isValid = true;
                var nameRegex = /^[a-zA-Z\s]+$/;
                var emailRegex = /^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/;

                var name = $('input[name="name"]').val();
                var email = $('input[name="email"]').val();
                var to_email = $('input[name="to_email"]').val();
                var message = $('textarea[name="message"]').val();

                if (!nameRegex.test(name)) {
                    alert('Please enter a valid name.');
                    isValid = false;
                }

                if (!emailRegex.test(email)) {
                    alert('Please enter a valid email address.');
                    isValid = false;
                }

                if (!emailRegex.test(to_email)) {
                    alert('Please enter a valid recipient email address.');
                    isValid = false;
                }
                if (message == "") {
                    alert('Please enter a message.');
                    isValid = false;
                }

                if (isValid) {
                    var confirmation = confirm("Please confirm that the email addresses are valid and correct.");
                    if (confirmation) {
                        $('#send-button').prop('disabled', true);
                        $('#loading').show(); // Show the loading indicator
                        var formData = $('#checkout-frm').serialize();
                        formData += '&edit_image=' + encodeURIComponent('<?php echo $editedImageURL; ?>');
                        formData += '&message=' + encodeURIComponent($('textarea[name="message"]').val());
                        // Adding edit_image_url parameter to formData
                        formData += '&edit_image_url=' + encodeURIComponent('<?php echo $editedImageURL; ?>');
                        // Capture delivery option and scheduled datetime from the form
                        var deliveryOption = $('select[name="delivery_option"]').val();
                        var scheduledDatetime = $('input[name="schedule_datetime"]').val();

                        // Include the delivery option and scheduled datetime in the formData
                        formData += '&delivery_option=' + encodeURIComponent(deliveryOption);
                        if (deliveryOption === 'scheduled' && scheduledDatetime) {
                            formData += '&schedule_datetime=' + encodeURIComponent(scheduledDatetime);
                        }

                        $.ajax({
                            url: "sendMail.php",
                            method: 'POST',
                            data: formData,
                            success: function(resp) {
                                $('#loading').hide(); // Hide the loading indicator
                                if (resp.trim() === 'Message has been sent and order saved successfully' || resp.trim() === 'Email has been scheduled successfully') {
                                    setTimeout(function() {
                                        location.replace('index.php?page=home'); // Redirect to home.php after a slight delay
                                    }, 1500);
                                } else {
                                    // Consider replacing this alert with a more user-friendly notification
                                    alert('Error from sendMail.php: ' + resp);
                                    $('#send-button').prop('disabled', false);
                                }
                            },
                            error: function() {
                                $('#loading').hide(); // Hide the loading indicator
                                // Consider replacing this alert with a more user-friendly notification
                                alert('Error during sending email.');
                                $('#send-button').prop('disabled', false);
                            }
                        });
                    }
                }
            });

            function saveToDatabase(formData) {
                $.ajax({
                    url: "admin/ajax.php?action=save_order",
                    method: 'POST',
                    data: formData,
                    success: function(resp) {
                        console.log('Response:', resp); // Log the response
                        if (resp.trim() === 'Order saved successfully') {
                            alert("Email successfully sent and order saved.");
                            setTimeout(function() {
                                location.replace('index.php?page=home'); // Redirect to home
                            }, 1500);
                        } else {
                            alert("Email was sent, but failed to save order: " + resp);
                            $('#send-button').prop('disabled', false);
                        }
                    },
                    error: function(error) {
                        alert('Ajax Error: ' + error.statusText); // Show the ajax error in an alert box
                        $('#send-button').prop('disabled', false);
                    }

                });
            }
        });



        // Get the canvas state from the PHP variable
        const canvasStateFromQuery = <?php echo json_encode($canvasState); ?>;

        // Initialize the Fabric canvas instance
        const savedCanvas = new fabric.Canvas('savedCanvas'); // Assuming you have a <canvas> element with id 'savedCanvas'

        // Load canvas state from the query parameter or session storage
        if (canvasStateFromQuery) {
            savedCanvas.loadFromJSON(canvasStateFromQuery, savedCanvas.renderAll.bind(savedCanvas));
            console.log('Canvas state loaded from query parameter');
        } else {
            const canvasId = '<?php echo $id; ?>'; // Replace with the same unique identifier
            const savedCanvasState = sessionStorage.getItem(canvasId);
            console.log('savedCanvasState:', savedCanvasState);
            if (savedCanvasState) {
                savedCanvas.loadFromJSON(JSON.parse(savedCanvasState), savedCanvas.renderAll.bind(savedCanvas));
                console.log('Canvas state loaded from session storage');
            }
        }
    </script>


</body>

</html>