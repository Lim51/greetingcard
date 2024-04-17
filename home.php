 <!-- Masthead-->
 <header class="masthead">
     <div class="container h-100">
         <div class="row h-100 align-items-center justify-content-center text-center">
             <div class="col-lg-10 align-self-center mb-4 page-title">
                 <h1 class="text-white">Welcome to <?php echo $_SESSION['setting_name']; ?></h1>
                 <hr class="divider my-4 bg-dark" />
                 <a class="btn btn-dark bg-black btn-xl js-scroll-trigger" href="#categories">Sending Now</a>

             </div>

         </div>
     </div>
 </header>
 <section class="page-section" id="categories">
     <h1 class="text-center text-cursive" style="font-size: 3em;"><b>Explore Cards</b></h1>
     <div class="d-flex justify-content-center">
         <hr class="border-dark" width="5%">
     </div>
     <div id="category-field" class="card-deck mt-2 justify-content-center">

         <?php
            require_once 'admin/db_connect.php';

            $status = 1;
            $limit = 10;
            $page = (isset($_GET['_page']) && $_GET['_page'] > 0) ? $_GET['_page'] : 1; // Corrected the page parameter check
            $offset = ($page - 1) * $limit;

            // Get total count of categories
            $categoriesCountQuery = "SELECT COUNT(id) as total_categories FROM category_list WHERE status = ?";
            $stmtCount = $conn->prepare($categoriesCountQuery);
            $stmtCount->bind_param("i", $status);
            $stmtCount->execute();
            $countResult = $stmtCount->get_result();
            $allCategory = $countResult->fetch_assoc()['total_categories'];
            $pageBtnCount = ceil($allCategory / $limit);

            // Fetch categories with pagination
            $categoriesQuery = "SELECT * FROM category_list WHERE status = ? ORDER BY name ASC LIMIT ? OFFSET ?";
            $stmt = $conn->prepare($categoriesQuery);
            $stmt->bind_param("iii", $status, $limit, $offset);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
            ?>
                 <div class="box-3 float-container">
                     <a href="index.php?page=category&id=<?= $row['id'] ?>">
                         <img src="assets/img/category/<?= $row['img_path'] ?>" class="img-responsive img-curve" alt="Category Image" style="height: 358px; width: 358px;">
                     </a>
                     <a href="index.php?page=category&id=<?= $row['id'] ?>"></a>
                     <h3 class="float-text"><?= $row['name'] ?></h3>
                 </div>
                 <div class="clearfix"></div>
         <?php
                }
            } else {
                // echo "No categories found!";
            }

            // Close the database connections and statements

            ?>
     </div>
 </section>



 </section>
 <section class="page-section" id="menu">
     <h1 class="text-center text-cursive" style="font-size:3em"><b>Cards</b></h1>
     <div class="d-flex justify-content-center">
         <hr class="border-dark" width="5%">
     </div>
     <!-- Search Box -->
     <div class="row mt-7">
         <div class="col-lg-3 offset-lg-9">
             <div class="input-group">
                 <input type="text" class="form-control" id="search_card" placeholder="Type to Search...">
                 <div class="input-group-append">
                     <button class="btn btn-outline-dark" type="button" id="btn_search">Search</button>
                 </div>
             </div>
         </div>
     </div>
     <br>

     <div id="menu-field" class="card-deck mt-2 justify-content-center">
         <?php
            include 'admin/db_connect.php';
            $stmt = $conn->prepare("SELECT * 
                        FROM card_list 
                        WHERE category_id IN 
                            (SELECT category_id 
                             FROM category_list 
                             WHERE status = ?)");
            $status = 1;
            $stmt->bind_param("i", $status);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $limit = 12;
                $page = (isset($_GET['_page']) && $_GET['_page'] > 0) ? $_GET['_page'] - 1 : 0;
                $offset = $page > 0 ? $page * $limit : 0;
                $stmt_count = $conn->prepare("SELECT id FROM card_list");
                $stmt_count->execute();
                $all_menu = $stmt_count->get_result()->num_rows;
                $page_btn_count = ceil($all_menu / $limit);
                $stmt = $conn->prepare("SELECT * FROM card_list WHERE status = ? ORDER BY `name` ASC LIMIT ? OFFSET ?");
                $stmt->bind_param("iii", $status, $limit, $offset);
                $stmt->execute();
                $result = $stmt->get_result();
                while ($row = $result->fetch_assoc()) :
            ?>
                 <div class="col-lg-3 mb-3">
                     <div class="card menu-item rounded-0">
                         <div class="position-relative overflow-hidden" id="item-img-holder">
                             <img src="assets/img/<?php echo $row['img_path'] ?>" class="card-img-top" alt="...">
                         </div>
                         <div class="card-body rounded-0">
                             <h5 class="card-title"><?php echo $row['name'] ?></h5>

                             <div class="text-center">
                                 <button class="btn btn-sm btn-outline-dark view_prod btn-block" data-id=<?php echo $row['id'] ?>><i class="fa fa-eye"></i> View</button>
                             </div>
                         </div>
                     </div>
                 </div>
         <?php endwhile;
            } else {
                echo "No cards found!";
            }
            ?>
     </div>
 </section>
 <?php //$page_btn_count = 10;exit; 
    ?>
 <!-- Pagination Buttons Block -->
 <div class="w-100 mx-4 d-flex justify-content-center">
     <div class="btn-group paginate-btns">
         <!-- Previous Page Button -->
         <a class="btn btn-default border border-dark" <?php echo ($page == 0) ? 'disabled' : '' ?> href="./?_page=<?php echo ($page) ?>">Prev.</a>
         <!-- End of Previous Page Button -->
         <!-- Pages Page Button -->

         <!-- looping page buttons  -->
         <?php for ($i = 1; $i <= $page_btn_count; $i++) : ?>
             <!-- Display button blocks  -->

             <!-- Limiting Page Buttons  -->
             <?php if ($page_btn_count >= 12) : ?>
                 <!-- Show ellipisis button before the last Page Button  -->
                 <?php if ($i = $page_btn_count && !in_array($i, range(($page - 3), ($page + 3)))) : ?>
                     <a class="btn btn-default border border-dark ellipsis">...</a>
                 <?php endif; ?>

                 <!-- Show ellipisis button after the First Page Button  -->
                 <?php if ($i == 1 || $i == $page_btn_count || (in_array($i, range(($page - 3), ($page + 3))))) : ?>
                     <a class="btn btn-default border border-dark <?php echo ($i == ($page + 1)) ? 'active' : '';  ?>" href="./?_page=<?php echo $i ?>"><?php echo $i; ?></a>
                     <?php if ($i == 1 && !in_array($i, range(($page - 3), ($page + 3)))) : ?>
                         <a class="btn btn-default border border-dark ellipsis">...</a>
                     <?php endif; ?>
                 <?php endif; ?>
             <?php else : ?>
                 <a class="btn btn-default border border-dark <?php echo ($i == ($page + 1)) ? 'active' : '';  ?>" href="./?_page=<?php echo $i ?>"><?php echo $i; ?></a>
             <?php endif; ?>
             <!-- Display button blocks  -->
         <?php endfor; ?>
         <!-- End of looping page buttons  -->

         <!-- End of Pages Page Button -->
         <!-- Next Page Button -->
         <a class="btn btn-default border border-dark" <?php echo (($page + 1) == $page_btn_count) ? 'disabled' : '' ?> href="./?_page=<?php echo ($page + 2) ?>">Next</a>
         <!-- End of Next Page Button -->
     </div>
 </div>
 <!-- End Pagination Buttons Block -->
 </section>
 <script>
     $('.view_prod').click(function() {
         uni_modal_right('Card Details', 'view_prod.php?id=' + $(this).attr('data-id'))
     })

     

     $('#btn_search').click(function() {
         performSearch();
     });

     $('#search_card').keypress(function(event) {
         if (event.which === 13) { // 13 is the keycode for Enter key
             event.preventDefault(); // Prevent the default Enter key behavior (form submission, etc.)
             performSearch();
         }
     });

     function performSearch() {
         var searchVal = $('#search_card').val().trim();
         if (searchVal !== '') {
             searchCards(searchVal);
         } else {
             loadAllCards();
         }
     }


     function searchCards(searchVal) {
         $.ajax({
             url: 'search_card.php',
             method: 'POST',
             data: {
                 search: searchVal
             },
             success: function(response) {
                 $('#menu-field').html(response);
             }
         });
     }

     function loadAllCards() {
         $.ajax({
             url: 'load_all_cards.php',
             method: 'GET',
             success: function(response) {
                 $('#menu-field').html(response);
             }
         });
     }
 </script>
 <?php if (isset($_GET['_page'])) : ?>
     <script>
         $(function() {
             document.querySelector('html').scrollTop = $('#menu').offset().top - 100
         })
     </script>
 <?php endif; ?>
 