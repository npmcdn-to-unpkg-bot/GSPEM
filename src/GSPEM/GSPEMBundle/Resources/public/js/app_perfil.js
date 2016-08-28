/**
 * Created by gabo on 26/07/16.
 */
GSPEMApp.controller('abmPerfil', function($scope,$http,$uibModal,toastr,MovPend) {
    $scope.passedite=true;
    $scope.pass="";
    var getPerfil = function() {
        $http.get(Routing.generate('get_profile')
        ).success(function (user) {
            $scope.userdata=user;
            $scope.id=$scope.userdata.id;
            $scope.nombre=$scope.userdata.name;
            $scope.apellido=$scope.userdata.lastName;
            $scope.perfil=$scope.userdata.view;
            $scope.mail=$scope.userdata.mail;
            $scope.user=$scope.userdata.username;
            //$scope.pass=$scope.userdata.password;
            $scope.phone=$scope.userdata.phone;

            console.log($scope.userdata);
        });
    };

    $scope.getActive= function(val1,val2){
        if(val1==val2)
            return "btn btn-default active"
        else
            return "btn btn-default";
    }

    $scope.setActiveUser= function(val1){
        $scope.perfil=val1;
    }

    $scope.editPass=function () {
        if($scope.passedite){
            $scope.passedite=false;
        }else {
            $scope.passedite=true;
        }
    }
    getPerfil();

    $scope.saveUser= function () {
        if ($scope.nombre.length == 0 || $scope.user.length == 0 || $scope.mail.length == 0 ) {
            toastr.warning('Complete todos los campos requeridos (*)', 'Atención');
            return false;
        }
        if(!$scope.passedite){
            if($scope.pass.length <6){
                toastr.warning('La clave no puede ser menor a 6 digitos', 'Atención');
                return false;
            }
        }else {
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
                toastr.success('Guardado con exito', 'Perfil');
            },
            function (response) { // optional
                // failed
            });
    };

});

