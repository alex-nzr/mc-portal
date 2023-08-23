BX.ready(function (){
    const ApproverList = BX.namespace("Cbit.Mc.Profile.PhotoApproveListComponent");

    ApproverList.init = function (params) {
        BX.SidePanel.Instance.bindAnchors({
            rules:
                [
                    {
                        condition: [
                            /\/profile\/approve\/photo\/detail.php\?([^]*?)/i
                        ],
                        //loader: "tasks:view-loader"
                    }
                ]
        });

        BX.addCustomEvent("SidePanel.Slider:onClose", function(event) {
            if (event.getSlider().getUrl().indexOf("/profile/approve/photo/detail.php") !== -1)
            {
                window.location.href = params.backUrl;
            }
        });
    }
})