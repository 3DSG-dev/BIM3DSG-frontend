/**
 * Box geometry node type
 *
 * Usage example:
 *
 * someNode.addNode({
 *      type: "prims/myGeometry",
 *      id: "filename",
 *      wire: false // Default
 *  });
 */
(function () {

    SceneJS.Types.addType("prims/myPointCloud", {
        construct: function (params) {

            var myPointCloudCacheManager = new PointCloudCacheManager();
            var that = this;
            myPointCloudCacheManager.fileReceived = function (jsondata) {
                params.jsondata = jsondata;
                that.addNode(build.call(that, params));
            }
            myPointCloudCacheManager.getFile(params.fid)
        }
    });

    function build(params) {

        var fid;
        if (params.fid) {
            fid = params.fid;
        }
        else {
            fid = "";
        }

        var coreId = "prims/myPointCloud" + fid + "_pointCloud";

        // If a node core already exists for a prim with the given properties,
        // then for efficiency we'll share that core rather than create another geometry
        if (this.getScene().hasCore("geometry", coreId)) {
            return {
                type: "geometry",
                coreId: coreId
            };
        }


        /*        var point = params.jsondata.pointList[0];
        var point2 = params.jsondata.pointList[15];
        // Otherwise, create a new geometry
        var result = {};

       return {
            type: "material",
            coreId: coreId+"1",
            color:{ r: point.color[0], g:point.color[1], b:point.color[2] },
            emit:0.9,
            nodes:[
                {
                    type:"geometry",
                    id:"points",
                    primitive:"points",
                    positions:[point.positions[0], point.positions[1], point.positions[2]],
                    pointSize:10
                }
            ]
        },
            {
                type: "material",
                coreId: coreId+"2",
                color:{ r: point2.color[0], g:point2.color[1], b:point2.color[2] },
                emit:0.9,
                nodes:[
                    {
                        type:"geometry",
                        id:"points",
                        primitive:"points",
                        positions:[point2.positions[0], point2.positions[1], point2.positions[2]],
                        pointSize:10
                    }
                ]
            };*/

/*         return {
         type: "geometry",
         primitive: "points",
         coreId: coreId,
         positions: [point.positions[0], point.positions[1], point.positions[2]],
             colors : [0, 0, 1, 1],
         pointSize:10
         };*/

       return {
            type: "geometry",
            primitive: "points",
            coreId: coreId,
            positions: params.jsondata.positions,
            colors: params.jsondata.colors,
            pointSize:1
        };

/*
        return {
            type: "geometry",
            primitive: "points",
            coreId: coreId,
            positions: params.jsondata.positions,
            pointSize:1
        };*/
    }
})();