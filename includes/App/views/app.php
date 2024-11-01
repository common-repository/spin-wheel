<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<div class="modal wof__micromodal-slide" id="wof__modal" aria-hidden="true">
	<div class="wof__modal__overlay" tabindex="-1" data-micromodal-close>
		<div class="wof__modal__container spin-wheel-wrapper" role="dialog" aria-modal="true">
			<div class="spin-wheel-spin-wrapper">
				<!-- close icon start -->
				<div class="spin-wheel-close-icon">
					<a href="#">
						<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
							stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
							class="icon icon-tabler icons-tabler-outline icon-tabler-x">
							<path stroke="none" d="M0 0h24v24H0z" fill="none" />
							<path d="M18 6l-12 12" />
							<path d="M6 6l12 12" />
						</svg>
					</a>
				</div>
				<!-- close icon end -->
				<div class="spin-wheel-spin-wrapper-el">

					<div id="spin-wheel" class="spin-wheel"></div>

					<div class="spin-wheel-forms-wrapper">

						<div class="spin-wheel-form-loader spin-wheel-hidden">
							<svg style="height:100px; width:100px;" xmlns="http://www.w3.org/2000/svg"
								viewBox="0 0 200 200">
								<circle fill="#FF156D" stroke="#FF156D" stroke-width="15" r="15" cx="40" cy="100">
									<animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;"
										keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.4">
									</animate>
								</circle>
								<circle fill="#FF156D" stroke="#FF156D" stroke-width="15" r="15" cx="100" cy="100">
									<animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;"
										keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="-.2">
									</animate>
								</circle>
								<circle fill="#FF156D" stroke="#FF156D" stroke-width="15" r="15" cx="160" cy="100">
									<animate attributeName="opacity" calcMode="spline" dur="2" values="1;0;1;"
										keySplines=".5 0 .5 1;.5 0 .5 1" repeatCount="indefinite" begin="0">
									</animate>
								</circle>
							</svg>
						</div>
						<div class="spin-wheel-spin-step">
							<form class="spin-wheel-forms-spin">
								<h2>Spin the wheel to win the prize</h2>
								<p>Give a try on your chance to win the prize!</p>
								<div class="spin-wheel-control-item">
									<input type="text" name="name" placeholder="Enter your name" required>
								</div>
								<div class="spin-wheel-control-item">
									<input type="email" name="email" placeholder="Enter your email" required>
								</div>
								<?php
								wp_nonce_field( 'wof_nonce', '_wpnonce', true, true );
								?>
								<div>
									<button id="spin-wheel-spin-btn" class="spin-wheel__spin" type="submit">Get
										Lucky</button>
									<!-- <a href="#" id="spin-wheel-dont-feel-lucky">No, I don't feel lucky.</a> -->
								</div>
							</form>
						</div>
						<div class="spin-wheel-win-step spin-wheel-hidden">
							<h2>Hey, You got {{prize}}</h2>
							<p>Copy your coupon code now.</p>
							<div class="spin-wheel-win-coupon-wrap">
								<pre><span id="spin-wheel-win-coupon"></span></pre>
								<button type="button">Copy</button>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>