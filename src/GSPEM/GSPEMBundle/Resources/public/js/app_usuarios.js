/**
 * Created by gabo on 26/07/16.
 */
GSPEMApp.controller('abmUsuarios', function($scope,$http,$uibModal,toastr,MovPend) {
    var getUsuarios = function() {
        $http.get(Routing.generate('get_users')
        ).success(function (users) {
            $scope.users=users;
            //console.log($scope.users);
        });
    };

    getUsuarios();

    $scope.deleteUser = function (id) {
        $http({
            url: Routing.generate('delete_usuario'),
            method: "POST",
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data: {
                id:id,
            },
            transformRequest: function (obj) {
                var str = [];
                for (var p in obj)
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                return str.join("&");
            }
        }).then(function (response) {
                getUsuarios();
            },
            function (response) { // optional
                // failed
            });
    };

    $scope.chaneStateUser=function (id,state) {
        var stateUser=1;
        if(state){
             stateUser=0;
        }
        $http({
            url: Routing.generate('save_users_state'),
            method: "POST",
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data: {
                id:id,
                state: stateUser
            },
            transformRequest: function (obj) {
                var str = [];
                for (var p in obj)
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                return str.join("&");
            }
        }).then(function (response) {
                getUsuarios();
            },
            function (response) { // optional
                // failed
            });
    };





    $scope.new = function (item,template , controller) {

        var modalInstance = $uibModal.open({
            templateUrl: template,
            controller: controller,
            resolve: {
                item: function () {
                    return item;
                }
            }
        });

        modalInstance.result.then(function (selectedItem) {
            $scope.selected = selectedItem;
        }, function () {
            getUsuarios();
            //$log.info('Modal dismissed at: ' + new Date());
        });
    };
});



GSPEMApp.controller('ModalNewUserCtrl', function($filter,$scope,$http, $uibModalInstance, item,toastr) {
    $scope.item = item;
    $scope.type=1;
    $scope.pass="";
    $scope.passedite=true;


    $scope.editPass=function () {
        if($scope.passedite){
            $scope.passedite=false;
        }else {
            $scope.passedite=true;
        }
    }

    if(item!=null){
        $scope.id=item.id;
        $scope.nombre=item.name;
        $scope.apellido=item.lastName;
        $scope.perfil=item.view;
        $scope.user=item.username;
        $scope.phone=item.phone;
        $scope.mail=item.mail;
    }else {
        $scope.nombre="";
        $scope.descript="";
        $scope.id=0;
        $scope.precio=0;
        $scope.precio1=0;
        $scope.perfil=1;
    }


    $scope.getActive= function(val1,val2){
        if(val1==val2)
            return "btn btn-default active"
        else
            return "btn btn-default";
    }

    $scope.setActiveUser= function(val1){
        $scope.perfil=val1;
    }


    $scope.cerrar=function () {
        $uibModalInstance.dismiss('cancel');
    };

    $scope.saveUser= function () {


        if ($scope.nombre.length == 0 || $scope.user.length == 0  || $scope.mail.length == 0 ) {
            toastr.warning('Complete todos los campos requeridos (*)', 'Atención');
            return false;
        }

        if(!$scope.passedite){
            if($scope.pass.length <6){
                toastr.warning('La clave no puede ser menor a 6 digitos', 'Atención');
                return false;
            }
        }else{
            $scope.pass="";
        }


        if($scope.mail.search("@")< 0 || $scope.mail.indexOf(".com")< 0){
            toastr.warning('Mail invalido', 'Atención');
            return false;
        }

        $http({
            url: Routing.generate('save_users'),
            method: "POST",
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            data: {
                nombre: $scope.nombre,
                apellido: $scope.apellido,
                id:$scope.id,
                username:$scope.user,
                password:$scope.pass,
                phone:$scope.phone,
                mail:$scope.mail,
                view:$scope.perfil
            },
            transformRequest: function (obj) {
                var str = [];
                for (var p in obj)
                    str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
                return str.join("&");
            }
        }).then(function (response) {
                console.log(response);
                $uibModalInstance.dismiss('cancel');
            },
            function (response) { // optional
                // failed
            });
    };
});
