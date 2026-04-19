<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Network Map - {{ config('app.name') }}</title>
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f5f5;
        }

        .header {
            background: #fff;
            padding: 20px 40px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .stats {
            display: flex;
            gap: 30px;
            margin-top: 15px;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 20px;
            font-weight: 600;
            color: #2563eb;
            margin-top: 4px;
        }

        .controls {
            padding: 20px 40px;
            background: #fff;
            border-bottom: 1px solid #e5e5e5;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }

        .btn {
            padding: 10px 20px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s;
        }

        .btn:hover {
            background: #1d4ed8;
        }

        .btn-secondary {
            background: #6b7280;
        }

        .btn-secondary:hover {
            background: #4b5563;
        }

        #mynetwork {
            width: 100%;
            height: calc(100vh - 250px);
            border: 1px solid #e5e5e5;
            background: #fff;
        }

        .container {
            padding: 20px 40px;
        }

        .legend {
            background: #fff;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .legend h3 {
            font-size: 14px;
            margin-bottom: 10px;
            color: #333;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 8px;
            font-size: 13px;
            color: #666;
        }

        .legend-arrow {
            width: 40px;
            height: 2px;
            background: #666;
            position: relative;
        }

        .legend-arrow::after {
            content: '';
            position: absolute;
            right: -4px;
            top: -3px;
            width: 0;
            height: 0;
            border-left: 8px solid #666;
            border-top: 4px solid transparent;
            border-bottom: 4px solid transparent;
        }

        .loading {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 16px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔗 Referral Network Map</h1>
        <div class="stats">
            <div class="stat-item">
                <span class="stat-label">Total Acquisitions</span>
                <span class="stat-value">{{ $stats['total_acquisitions'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Unique Users</span>
                <span class="stat-value">{{ $stats['unique_users'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Referrers</span>
                <span class="stat-value">{{ $stats['referrers_count'] }}</span>
            </div>
            <div class="stat-item">
                <span class="stat-label">Acquired Users</span>
                <span class="stat-value">{{ $stats['acquired_count'] }}</span>
            </div>
        </div>
    </div>

    <div class="controls">
        <button class="btn" onclick="fitNetwork()">Fit to Screen</button>
        <button class="btn btn-secondary" onclick="resetZoom()">Reset Zoom</button>
        <button class="btn btn-secondary" onclick="togglePhysics()">Toggle Physics</button>
        <button class="btn btn-secondary" onclick="exportImage()">Export as Image</button>
    </div>

    <div class="container">
        <div class="legend">
            <h3>Legend</h3>
            <div class="legend-item">
                <div class="legend-arrow"></div>
                <span>Referral relationship (Referrer → Acquired User)</span>
            </div>
            <div class="legend-item">
                <span style="width: 20px; height: 20px; border-radius: 50%; background: #97c2fc; border: 2px solid #2b7ce9;"></span>
                <span>User Node (Hover for details, Click to highlight connections)</span>
            </div>
        </div>

        <div id="mynetwork">
            <div class="loading">Loading network...</div>
        </div>
    </div>

    <script type="text/javascript">
        let network;
        let physicsEnabled = true;

        // Initialize the network
        function initNetwork() {
            // Create arrays for nodes and edges
            const nodes = new vis.DataSet(@json($nodes));
            const edges = new vis.DataSet(@json($edges));

            // Create a network
            const container = document.getElementById('mynetwork');
            const data = {
                nodes: nodes,
                edges: edges
            };

            const options = {
                nodes: {
                    shape: 'dot',
                    size: 16,
                    font: {
                        size: 14,
                        face: 'Arial'
                    },
                    borderWidth: 2,
                    shadow: true,
                    color: {
                        border: '#2b7ce9',
                        background: '#97c2fc',
                        highlight: {
                            border: '#d62728',
                            background: '#ff7f7f'
                        },
                        hover: {
                            border: '#ff7f0e',
                            background: '#ffbb78'
                        }
                    }
                },
                edges: {
                    width: 2,
                    color: {
                        color: '#848484',
                        highlight: '#d62728',
                        hover: '#ff7f0e'
                    },
                    arrows: {
                        to: {
                            enabled: true,
                            scaleFactor: 0.5
                        }
                    },
                    smooth: {
                        type: 'continuous',
                        roundness: 0.5
                    },
                    shadow: true
                },
                physics: {
                    enabled: true,
                    barnesHut: {
                        gravitationalConstant: -2000,
                        centralGravity: 0.3,
                        springLength: 95,
                        springConstant: 0.04,
                        damping: 0.09,
                        avoidOverlap: 0.1
                    },
                    stabilization: {
                        iterations: 150
                    }
                },
                interaction: {
                    hover: true,
                    tooltipDelay: 100,
                    navigationButtons: true,
                    keyboard: true
                },
                layout: {
                    improvedLayout: true,
                    hierarchical: {
                        enabled: false
                    }
                }
            };

            network = new vis.Network(container, data, options);

            // Event listeners
            network.on('stabilizationIterationsDone', function () {
                console.log('Network stabilized');
            });

            network.on('click', function (params) {
                if (params.nodes.length > 0) {
                    highlightConnections(params.nodes[0]);
                }
            });

            // Fit the network after stabilization
            network.once('stabilizationIterationsDone', function() {
                network.fit({
                    animation: {
                        duration: 1000,
                        easingFunction: 'easeInOutQuad'
                    }
                });
            });
        }

        function highlightConnections(nodeId) {
            const connectedNodes = network.getConnectedNodes(nodeId);
            const connectedEdges = network.getConnectedEdges(nodeId);
            
            console.log(`User ${nodeId} connections:`, {
                total: connectedNodes.length,
                nodes: connectedNodes
            });
        }

        function fitNetwork() {
            network.fit({
                animation: {
                    duration: 500,
                    easingFunction: 'easeInOutQuad'
                }
            });
        }

        function resetZoom() {
            network.moveTo({
                scale: 1.0,
                animation: {
                    duration: 500,
                    easingFunction: 'easeInOutQuad'
                }
            });
        }

        function togglePhysics() {
            physicsEnabled = !physicsEnabled;
            network.setOptions({ physics: { enabled: physicsEnabled } });
            console.log('Physics', physicsEnabled ? 'enabled' : 'disabled');
        }

        function exportImage() {
            const canvas = document.querySelector('#mynetwork canvas');
            if (canvas) {
                const link = document.createElement('a');
                link.download = 'referral-network-map.png';
                link.href = canvas.toDataURL();
                link.click();
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initNetwork();
        });
    </script>
</body>
</html>
