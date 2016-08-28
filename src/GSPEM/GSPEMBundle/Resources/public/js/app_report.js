/**
 * Created by gabo on 26/07/16.
 */

GSPEMApp.controller('abmReports', function($scope,$http,$uibModal,toastr,MovPend) {
    $scope.animationsEnabled = false;


    var getStock = function() {
        $http.get(Routing.generate('get_stock')
        ).success(function (stock) {
            $scope.stock=stock;
            //console.log($scope.stock);
        });
    };
    getStock();

    var getUsers= function () {
        $http.get(Routing.generate('get_users')
        ).success(function (data) {
            $scope.tecnicos=data;
            $scope.tecnicotarea=$scope.tecnicos[0];
            getStockFromUser();
        });
    }
    getUsers();



    var getSitios = function() {
        $http.get(Routing.generate('get_sitios')
        ).success(function (sitios) {
            $scope.sitios=sitios;
            $scope.sitioselected=$scope.sitios[0];
            getStockFromSite();
        });
    };
    getSitios();

    $scope.updateReportUsers=function () {
        getStockFromUser();
    };

    $scope.updateReportSitios=function () {
        getStockFromSite();
    };
    var getStockFromUser=function () {

        $http({
            url: Routing.generate('get_stock_user_custom'),
            method: "POST",
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data: {
                id:  $scope.tecnicotarea.id
            },
            transformRequest: function (obj) {
                var str = [];
                for (var p in obj)
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                return str.join("&");
            }
        }).then(function (response) {
            console.log(response);
                $scope.stock_tec=response.data;
            },
            function (response) { // optional
                // failed
            });
    }

    var getStockFromSite=function () {

        $http({
            url: Routing.generate('get_stock_sitio_custom'),
            method: "POST",
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data: {
                id:  $scope.sitioselected.id
            },
            transformRequest: function (obj) {
                var str = [];
                for (var p in obj)
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                return str.join("&");
            }
        }).then(function (response) {
                console.log(response);
                $scope.stock_sit=response.data;
            },
            function (response) { // optional
                // failed
            });
    }

    $scope.confirmar=function () {

        var blob = new Blob([document.getElementById('exportable').innerHTML], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8"
        });
        saveAs(blob, "ReportMaestro.xls");
    };


    $scope.exportarStockUser=function () {

        var blob = new Blob([document.getElementById('exportable').innerHTML], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8"
        });
        saveAs(blob, "Reporte_"+$scope.tecnicotarea.name+".xls");
    };

    $scope.exportarStockSitio=function () {

        var blob = new Blob([document.getElementById('exportable').innerHTML], {
            type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8"
        });
        saveAs(blob, "Reporte_"+$scope.sitioselected.name+".xls");
    };
});