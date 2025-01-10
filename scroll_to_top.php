<!-- scroll-to-top.php -->

<button id="scrollToTopBtn" title="Back To Top"><i class="fas fa-arrow-up"></i></button>

<style>
    /* CSS for the scroll-to-top button */
    #scrollToTopBtn {
        display: none;
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 99;
        cursor: pointer;
        background-color: #007bff;
        color: white;
        border: none;
        border-radius: 55%;
        width: 55px;
        height: 45px;
        text-align: center;
        box-shadow: 0px 0px 10px 0px rgba(0, 0, 0, 0.1);
    }
</style>

<script>
    // JavaScript for the scroll-to-top button
    var scrollToTopBtn = document.getElementById("scrollToTopBtn");

    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            scrollToTopBtn.style.display = "block";
        } else {
            scrollToTopBtn.style.display = "none";
        }
    }

    scrollToTopBtn.addEventListener("click", function() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    });
</script>
