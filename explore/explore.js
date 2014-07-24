var app = angular.module("plExplore", []).controller('explore', ['$scope', '$http',
    function ($scope, $http) {
        $scope.loadingResults = true;

        $scope.onResultObtained = function (response) {
            console.log(response);
            setup($scope);
        }
        $scope.onVizLoad = function () {
        		$scope.$apply(function(){
            	$scope.loadingResults = false;
            });
        }
        $scope.onSearchButtonClick = function () {
        		$scope.loadingResults = true;
            $("#graph > svg").remove();
        }
        // Set up the monster visualization
        $(document).ready(function () {
            setup($scope);
        });
    }
]);

function setup($scope) {
    // SETUP
    var a = 1200, // width of svg (originally 800)
        c = 800, // height of svg (originally 800)
        h = c,
        U = 200,
        K = 22,
        S = 20,
        s = 8,
        R = 110,
        J = 30,
        o = 15,
        t = 10,
        w = 1000,
        F = "elastic",
        N = "#7D0000"; // color on hover
    var T, q, x, j, H, A, P;
    var L = {},
        k = {};
    var i, y;
    var r = d3.layout.tree().size([360, h / 2 - R]).separation(function (Y, X) {
        return (Y.parent == X.parent ? 1 : 2) / Y.depth
    });
    var W = d3.svg.diagonal.radial().projection(function (X) {
        return [X.y, X.x / 180 * Math.PI]
    });
    var v = d3.svg.line().x(function (X) {
        return X[0]
    }).y(function (X) {
        return X[1]
    }).interpolate("bundle").tension(0.5);

    // APPEND SVG
    var d = d3.select("#graph").append("svg").attr("width", a).attr("height", c).append("g").attr("transform", "translate(" + a / 2 + "," + c / 2 + ")");
    var I = d.append("rect").attr("class", "bg").attr({
        x: a / -2,
        y: c / -2,
        width: a,
        height: c,
        fill: "transparent"
    }).on("click", O);
    var B = d.append("g").attr("class", "links"),
        f = d.append("g").attr("class", "episodes"),
        E = d.append("g").attr("class", "nodes");
    var Q = d3.select("#graph-info");
    d3.json("./explore/big_visualizer.php", function (Y) {
        data = Y;
        console.log(data);
        T = d3.map(Y);
        q = d3.merge(T.values()); // merge all objects into one array
        x = {};
        //A = d3.map();

        // x is object with all objects together by key (all nodes + episodes here) 
        // A contains all the linked concepts ( we don't need this now)
        q.forEach(function (aa) {
            aa.key = p(aa.name);
            aa.canonicalKey = aa.key;
            x[aa.key] = aa;
            /*if (aa.group) {
                if (!A.has(aa.group)) {
                    A.set(aa.group, [])
                }
                A.get(aa.group).push(aa)
            }
            */
        });



        j = d3.map();
        // go through each episode and push links by node to j
        // each item in j is an array of linked objects (key of j are objects) 
        T.get("episodes").forEach(function (aa) {
            aa.links = aa.links.filter(function (ab) {
                return typeof x[p(ab)] !== "undefined" && ab.indexOf("r-") !== 0
            });
            j.set(aa.key, aa.links.map(function (ab) {
                var ac = p(ab);
                if (typeof j.get(ac) === "undefined") {
                    j.set(ac, [])
                }
                j.get(ac).push(aa);
                return x[ac]
            }))
        });

        // Z pulls out slug after # in url
        // if you've clicked on a particular node, then call G(x[Z])
        var Z = window.location.hash.substring(1);
        if (Z && x[Z]) {
            G(x[Z])
                // else call O() and M()
        } else {
            O();
            M()
        }
        window.onhashchange = function () {
            var aa = window.location.hash.substring(1);
            if (aa && x[aa]) {
                G(x[aa], true)
            }
        }
        $scope.onVizLoad();
    });


    // O() populates links (H object)
    function O() {
        if (L.node === null) {
            return
        }
        L = {
            node: null,
            map: {}
        };
        i = Math.floor(c / T.get("episodes").length);
        y = Math.floor(T.get("episodes").length * i / 2);
        T.get("episodes").forEach(function (af, ae) {
            af.x = U / -2;
            af.y = ae * i - y
        });
        var ad = 180 + J,
            Z = 360 - J,
            ac = (Z - ad) / (T.get("themes").length - 1);
        T.get("themes").forEach(function (af, ae) {
            af.x = Z - ae * ac;
            af.y = h / 2 - R;
            af.xOffset = -S;
            af.depth = 1
        });
        ad = J;
        Z = 180 - J;
        ac = (Z - ad) / (T.get("perspectives").length - 1);
        T.get("perspectives").forEach(function (af, ae) {
            af.x = ae * ac + ad;
            af.y = h / 2 - R;
            af.xOffset = S;
            af.depth = 1
        });

        // H is an array that stores all links
        // each link is an object with the following keys:
        // canonicalKey, key, source, target, x1, x2, y1, y2
        H = [];
        var ab, Y, aa, X = h / 2 - R;
        T.get("episodes").forEach(function (ae) {
            ae.links.forEach(function (af) {
                ab = x[p(af)];
                if (!ab || ab.type === "reference") {
                    return
                }
                Y = (ab.x - 90) * Math.PI / 180;
                aa = ae.key + "-to-" + ab.key;
                H.push({
                    source: ae,
                    target: ab,
                    key: aa,
                    canonicalKey: aa,
                    x1: ae.x + (ab.type === "theme" ? 0 : U),
                    y1: ae.y + K / 2,
                    x2: Math.cos(Y) * X + ab.xOffset,
                    y2: Math.sin(Y) * X
                })
            })
        });

        // P is array of pair coordinates
        /*
        P = [];
        A.forEach(function (af, ag) {
            var ae = (ag[0].x - 90) * Math.PI / 180;
            a2 = (ag[1].x - 90) * Math.PI / 180, bulge = 20;
            P.push({
                x1: Math.cos(ae) * X + ag[0].xOffset,
                y1: Math.sin(ae) * X,
                xx: Math.cos((ae + a2) / 2) * (X + bulge) + ag[0].xOffset,
                yy: Math.sin((ae + a2) / 2) * (X + bulge),
                x2: Math.cos(a2) * X + ag[1].xOffset,
                y2: Math.sin(a2) * X
            })
        });
        */

        window.location.hash = "";
        M()
    }

    function G(Y, X) {
        if (L.node === Y && X !== true) {
            if (Y.type === "episode") {
                window.location.href = "/" + Y.slug;
                return
            }
            L.node.children.forEach(function (aa) {
                aa.children = aa._group
            });
            e();
            return
        }
        if (Y.isGroup) {
            L.node.children.forEach(function (aa) {
                aa.children = aa._group
            });
            Y.parent.children = Y.parent._children;
            e();
            return
        }
        Y = x[Y.canonicalKey];
        q.forEach(function (aa) {
            aa.parent = null;
            aa.children = [];
            aa._children = [];
            aa._group = [];
            aa.canonicalKey = aa.key;
            aa.xOffset = 0
        });
        L.node = Y;
        L.node.children = j.get(Y.canonicalKey);
        L.map = {};
        var Z = 0;
        L.node.children.forEach(function (ac) {
            L.map[ac.key] = true;
            ac._children = j.get(ac.key).filter(function (ad) {
                return ad.canonicalKey !== Y.canonicalKey
            });
            ac._children = JSON.parse(JSON.stringify(ac._children));
            ac._children.forEach(function (ad) {
                ad.canonicalKey = ad.key;
                ad.key = ac.key + "-" + ad.key;
                L.map[ad.key] = true
            });
            var aa = ac.key + "-group",
                ab = ac._children.length;
            ac._group = [{
                isGroup: true,
                key: aa + "-group-key",
                canonicalKey: aa,
                name: ab,
                count: ab,
                xOffset: 0
            }];
            L.map[aa] = true;
            Z += ab
        });
        L.node.children.forEach(function (aa) {
            aa.children = Z > 50 ? aa._group : aa._children
        });
        window.location.hash = L.node.key;
        e()
    }

    function n() {
        k = {
            node: null,
            map: {}
        };
        z()
    }

    function g(X) {
        if (k.node === X) {
            return
        }
        k.node = X;
        k.map = {};
        k.map[X.key] = true;
        if (X.key !== X.canonicalKey) {
            k.map[X.parent.canonicalKey] = true;
            k.map[X.parent.canonicalKey + "-to-" + X.canonicalKey] = true;
            k.map[X.canonicalKey + "-to-" + X.parent.canonicalKey] = true
        } else {
            j.get(X.canonicalKey).forEach(function (Y) {
                k.map[Y.canonicalKey] = true;
                k.map[X.canonicalKey + "-" + Y.canonicalKey] = true
            });
            H.forEach(function (Y) {
                if (k.map[Y.source.canonicalKey] && k.map[Y.target.canonicalKey]) {
                    k.map[Y.canonicalKey] = true
                }
            })
        }
        z()
    }

    function M() {
        V();
        B.selectAll("path").attr("d", function (X) {
            return v([
                [X.x1, X.y1],
                [X.x1, X.y1],
                [X.x1, X.y1]
            ])
        }).transition().duration(w).ease(F).attr("d", function (X) {
            return v([
                [X.x1, X.y1],
                [X.target.xOffset * s, 0],
                [X.x2, X.y2]
            ])
        });
        D(T.get("episodes"));
        b(d3.merge([T.get("themes"), T.get("perspectives")]));
        C([]);
        //m(P);
        //Q.html('<a href="/the-concept-map/">What\'s this?</a>');
        n();
        z()
    }

    function e() {
        var X = r.nodes(L.node);
        X.forEach(function (Z) {
            if (Z.depth === 1) {
                Z.y -= 20
            }
        });
        H = r.links(X);
        H.forEach(function (Z) {
            if (Z.source.type === "episode") {
                Z.key = Z.source.canonicalKey + "-to-" + Z.target.canonicalKey
            } else {
                Z.key = Z.target.canonicalKey + "-to-" + Z.source.canonicalKey
            }
            Z.canonicalKey = Z.key
        });
        V();
        B.selectAll("path").transition().duration(w).ease(F).attr("d", W);
        D([]);
        b(X);
        C([L.node]);
        m([]);
        var Y = "";
        if (L.node.description) {
            Y = L.node.description
        }
        Q.html(Y);
        n();
        z()
    }

    function b(X) {
        var X = E.selectAll(".node").data(X, u);
        var Y = X.enter().append("g").attr("transform", function (aa) {
            var Z = aa.parent ? aa.parent : {
                xOffset: 0,
                x: 0,
                y: 0
            };
            return "translate(" + Z.xOffset + ",0)rotate(" + (Z.x - 90) + ")translate(" + Z.y + ")"
        }).attr("class", "node").on("mouseover", g).on("mouseout", n).on("click", G);
        Y.append("circle").attr("r", 0);
        Y.append("text").attr("stroke", "#fff").attr("stroke-width", 4).attr("class", "label-stroke");
        Y.append("text").attr("font-size", 0).attr("class", "label");
        X.transition().duration(w).ease(F).attr("transform", function (Z) {
            if (Z === L.node) {
                return null
            }
            var aa = Z.isGroup ? Z.y + (7 + Z.count) : Z.y;
            return "translate(" + Z.xOffset + ",0)rotate(" + (Z.x - 90) + ")translate(" + aa + ")"
        });
        X.selectAll("circle").transition().duration(w).ease(F).attr("r", function (Z) {
            if (Z == L.node) {
                return 100
            } else {
                if (Z.isGroup) {
                    return 7 + Z.count
                } else {
                    return 4.5
                }
            }
        });
        X.selectAll("text").transition().duration(w).ease(F).attr("dy", ".3em").attr("font-size", function (Z) {
            if (Z.depth === 0) {
                return 20
            } else {
                return 15
            }
        }).text(function (Z) {
            return Z.name
        }).attr("text-anchor", function (Z) {
            if (Z === L.node || Z.isGroup) {
                return "middle"
            }
            return Z.x < 180 ? "start" : "end"
        }).attr("transform", function (Z) {
            if (Z === L.node) {
                return null
            } else {
                if (Z.isGroup) {
                    return Z.x > 180 ? "rotate(180)" : null
                }
            }
            return Z.x < 180 ? "translate(" + t + ")" : "rotate(180)translate(-" + t + ")"
        });
        X.selectAll("text.label-stroke").attr("display", function (Z) {
            return Z.depth === 1 ? "block" : "none"
        });
        X.exit().remove()
    }

    function V() {
        var X = B.selectAll("path").data(H, u);
        X.enter().append("path").attr("d", function (Z) {
            var Y = Z.source ? {
                x: Z.source.x,
                y: Z.source.y
            } : {
                x: 0,
                y: 0
            };
            return W({
                source: Y,
                target: Y
            })
        }).attr("class", "link");
        X.exit().remove()
    }

    function C(Z) {
        var ac = d.selectAll(".detail").data(Z, u);
        var Y = ac.enter().append("g").attr("class", "detail");
        var ab = Z[0];
        if (ab && ab.type === "episode") {
            /*
                var aa = Y.append("a").attr("xlink:href", function (ae) {
                    return "/" + ae.slug
                });
                aa.append("text").attr("fill", N).attr("text-anchor", "middle").attr("y", (o + t) * -1).text(function (ae) {
                    return "EPISODE " + ae.episode
        
                })
            */
        } else {
            if (ab && ab.type === "theme") {
                Y.append("text").attr("fill", "#aaa").attr("text-anchor", "middle").attr("y", (o + t) * -1).text("DEPARTMENT")
            } else {
                if (ab && ab.type === "perspective") {
                    Y.append("text").attr("fill", "#aaa").attr("text-anchor", "middle").attr("y", (o + t) * -1).text("TOPIC")
                        /*
                        var ad = ac.selectAll(".pair").data(A.get(ab.group).filter(function (ae) {
                            return ae !== ab
                        }), u);
                        ad.enter().append("text").attr("fill", "#aaa").attr("text-anchor", "middle").attr("y", function (af, ae) {
                            return (o + t) * 2 + (ae * (o + t))
                        }).text(function (ae) {
                            return "(vs. " + ae.name + ")"
                        }).attr("class", "pair").on("click", G);
                        Y.append("text").attr("fill", "#aaa").attr("text-anchor", "middle").attr("y", (o + t) * -1).text("PERSPECTIVE");
                        ad.exit().remove()
                        */
                }
            }
        }
        ac.exit().remove();
        var X = d.selectAll(".all-episodes").data(Z);
        X.enter().append("text").attr("text-anchor", "start").attr("x", a / -2 + t).attr("y", c / 2 - t).text("RESET").attr("class", "all-episodes").on("click", O);
        X.exit().remove()
    }

    function D(Y) {
        var Y = f.selectAll(".episode").data(Y, u);
        var X = Y.enter().append("g").attr("class", "episode").on("mouseover", g).on("mouseout", n).on("click", G);
        X.append("rect").attr("x", U / -2).attr("y", K / -2).attr("width", U).attr("height", K).transition().duration(w).ease(F).attr("x", function (Z) {
            return Z.x
        }).attr("y", function (Z) {
            return Z.y
        });
        X.append("text").attr("x", function (Z) {
            return U / -2 + t
        }).attr("y", function (Z) {
            return K / -2 + o
        }).attr("fill", "#fff").text(function (Z) {
            return Z.name
        }).transition().duration(w).ease(F).attr("x", function (Z) {
            return Z.x + t
        }).attr("y", function (Z) {
            return Z.y + o
        });
        Y.exit().selectAll("rect").transition().duration(w).ease(F).attr("x", function (Z) {
            return U / -2
        }).attr("y", function (Z) {
            return K / -2
        });
        Y.exit().selectAll("text").transition().duration(w).ease(F).attr("x", function (Z) {
            return U / -2 + t
        }).attr("y", function (Z) {
            return K / -2 + o
        });
        Y.exit().transition().duration(w).remove()
    }


    // m connects opposing perspectives (don't need it), but it is useful once when we call m([])
    function m(Y) {
        var X = f.selectAll("path").data(Y);
        X.enter().append("path").attr("d", function (Z) {
            return v([
                [Z.x1, Z.y1],
                [Z.x1, Z.y1],
                [Z.x1, Z.y1]
            ])
        }).attr("stroke", "#000").attr("stroke-width", 1.5).transition().duration(w).ease(F).attr("d", function (Z) {
            return v([
                [Z.x1, Z.y1],
                [Z.xx, Z.yy],
                [Z.x2, Z.y2]
            ])
        });
        X.exit().remove()
    }


    function z() {
        f.selectAll("rect").attr("fill", function (X) {
            return l(X, "#000", N, "#000")
        });
        B.selectAll("path").attr("stroke", function (X) {
            return l(X, "#aaa", N, "#aaa")
        }).attr("stroke-width", function (X) {
            return l(X, "1.5px", "2.5px", "1px")
        }).attr("opacity", function (X) {
            return l(X, 0.4, 0.75, 0.3)
        }).sort(function (Y, X) {
            if (!k.node) {
                return 0
            }
            var aa = k.map[Y.canonicalKey] ? 1 : 0,
                Z = k.map[X.canonicalKey] ? 1 : 0;
            return aa - Z
        });
        E.selectAll("circle").attr("fill", function (X) {
            if (X === L.node) {
                return "#000"
            } else {
                if (X.type === "theme") {
                    return l(X, "#666", N, "#000")
                } else {
                    if (X.type === "perspective") {
                        return "#fff"
                    }
                }
            }
            return l(X, "#000", N, "#999")
        }).attr("stroke", function (X) {
            if (X === L.node) {
                return l(X, null, N, null)
            } else {
                if (X.type === "theme") {
                    return "#000"
                } else {
                    if (X.type === "perspective") {
                        return l(X, "#000", N, "#000")
                    }
                }
            }
            return null
        }).attr("stroke-width", function (X) {
            if (X === L.node) {
                return l(X, null, 2.5, null)
            } else {
                if (X.type === "theme" || X.type === "perspective") {
                    return 1.5
                }
            }
            return null
        });
        E.selectAll("text.label").attr("fill", function (X) {
            return (X === L.node || X.isGroup) ? "#fff" : l(X, "#000", N, "#999")
        })
    }

    function p(X) {
        return X.toLowerCase().replace(/[ .,()]/g, "-")
    }

    function u(X) {
        return X.key
    }

    function l(X, aa, Z, Y) {
        if (k.node === null) {
            return aa
        }
        return k.map[X.key] ? Z : aa
    }
}