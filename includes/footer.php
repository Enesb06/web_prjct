<!-- YENİ LOTTIE ANİMASYON KATMANI (İKİ ANİMASYONLU) -->
<div class="lottie-overlay" id="animation-overlay">
    <!-- Sulama Animasyonu (Başlangıçta gizli) -->
    <lottie-player 
        id="lottie-water-player" 
        class="lottie-animation"
        src="assets/animations/watering-animation.json"  
        background="transparent"  
        speed="1"  
        style="width: 250px; height: 250px; display: none;">
    </lottie-player>

    <!-- Gübreleme Animasyonu (Başlangıçta gizli) -->
    <lottie-player 
        id="lottie-fertilize-player" 
        class="lottie-animation"
        src="assets/animations/fertilizing-animation.json"  
        background="transparent"  
        speed="1"  
        style="width: 250px; height: 250px; display: none;">
    </lottie-player>
</div>


</div> <!-- .container kapanışı -->
    <script src="assets/script.js"></script>
</body>
</html>