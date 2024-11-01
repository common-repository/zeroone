jQuery(function($) {
	$(document).ready(function() {
		$(document).on("submit", "#mainform", function(e) {
			var mForm = $(this);
			if(mForm.find(".keysenderec-setup").length) {
				e.preventDefault();
				var container = mForm.find(".keysenderec-setup");

				if(container.hasClass("doing-ajax")) return false;
				container.addClass("doing-ajax");

				var apiKey = container.find(".api-key").val();
				var apiSecret = container.find(".api-secret").val();
				if(apiKey == "" || apiSecret == "") return false;

				$.ajax({
					type: "POST",
					url: WCKeysenderData.ajaxUrl,
					cache: false,
					dataType: "json",
					data: {
						action: "keysenderec_setup_api",
						apiKey: apiKey,
						apiSecret: apiSecret,
						nonce: WCKeysenderData.nonce
					}
				}).done(function(response) {
					if(!response.success) {
						alert(response.data.error);
						container.find(".api-key").val("");
						container.find(".api-secret").val("");
					} else {
						container.find(".api-key").attr("type", "password").attr("disabled", true);
						container.find(".api-secret").attr("type", "password").attr("disabled", true);
					}
					container.find(".keysenderec-info").html(response.data.indicator);
					container.removeClass("doing-ajax");
				}).fail(function() {
					container.removeClass("doing-ajax");
					alert("Error");
				});
			}
		});

		$(document).on("click", ".keysenderec-disconnect a", function(e) {
			e.preventDefault();

			var container = $(this).closest(".keysenderec-setup");

			if(container.hasClass("doing-ajax")) return false;
			container.addClass("doing-ajax");

			$.ajax({
				type: "POST",
				url: WCKeysenderData.ajaxUrl,
				cache: false,
				dataType: "json",
				data: {
					action: "keysenderec_disconnect_api",
					nonce: WCKeysenderData.nonce
				}
			}).done(function(response) {
				if(!response.success) {
					alert(response.data.error);
				} else {
					container.find(".api-key").val("").attr("type", "text").removeAttr("disabled");
					container.find(".api-secret").val("").attr("type", "text").removeAttr("disabled");
				}
				container.find(".keysenderec-info").html(response.data.indicator);
				container.removeClass("doing-ajax");
			}).fail(function() {
				container.removeClass("doing-ajax");
				alert("Error");
			});

		});
	});
});