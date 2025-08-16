      </div>
      <p style="font-size:12px;color:#aaa;text-align:center;margin-top:20px">
        <?php
        echo wp_kses(
            sprintf(
                __( 'Powered by %1$s – %2$s', 'obti' ),
                '<a href="https://www.totaliweb.com" style="color:#16a34a">Totaliweb</a>',
                '<a href="https://www.totaliweb.com" style="color:#16a34a">https://www.totaliweb.com</a>'
            ),
            [ 'a' => [ 'href' => [], 'style' => [] ] ]
        );
        ?>
      </p>
    </div>
  </body>
</html>
