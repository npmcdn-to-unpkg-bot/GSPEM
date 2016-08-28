/**
 * Created by gabo on 26/07/16.
 */

GSPEMApp.controller('abmStockMaestro', function($scope,$http,$uibModal,toastr,MovPend) {
    $scope.animationsEnabled = false;


    var getStock = function() {
        $http.get(Routing.generate('get_stock')
        ).success(function (stock) {
            $scope.stock=stock;
            //console.log($scope.stock);
        });
    };
    getStock();


    $scope.setStock=function (item) {
      //console.log(item.cant);

        if(item.cant!="" && item.cant!=undefined){
            item.stock=parseInt(item.cant) + parseInt(item.stock);
            item.new_stock=item.stock;

        }
    };


    $scope.confirmar=function () {

        $http({
            url: Routing.generate('set_stock'),
            method: "POST",
            headers: {'Content-Type': 'application/json'},
            data: {
                items:$scope.stock
            }
        }).then(function (response) {
                console.log(response);
                getStock();
                toastr.success('Guardado con éxito', 'Stock');
            },
            function (response) { // optional
                // failed
            });

    }

});