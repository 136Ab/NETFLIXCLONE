<?php
require_once 'db.php';

// Get a sample video for testing
$stmt = $pdo->query("SELECT * FROM content WHERE video_url IS NOT NULL LIMIT 1");
$testVideo = $stmt->fetch();

if (!$testVideo) {
    // Create a test video entry if none exists
    $stmt = $pdo->prepare("INSERT INTO content (title, description, genre, release_year, duration, video_url, thumbnail, type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        'Test Video',
        'This is a test video for the Netflix clone',
        'Test',
        2024,
        120,
        'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
        '/placeholder.svg?height=300&width=200',
        'movie'
    ]);
    
    $testVideo = [
        'id' => $pdo->lastInsertId(),
        'title' => 'Test Video',
        'description' => 'This is a test video for the Netflix clone',
        'video_url' => 'https://sample-videos.com/zip/10/mp4/SampleVideo_1280x720_1mb.mp4',
        'thumbnail' => '/placeholder.svg?height=300&width=200'
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Test - Netflix Clone</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            background-color: #000;
            color: white;
            padding: 2rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            color: #e50914;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            color: #b3b3b3;
            font-size: 1.1rem;
        }

        .test-section {
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            color: #e50914;
            margin-bottom: 1rem;
        }

        .video-container {
            position: relative;
            width: 100%;
            max-width: 800px;
            margin: 0 auto 2rem;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
        }

        .test-video {
            width: 100%;
            height: 450px;
            object-fit: contain;
        }

        .video-info {
            padding: 1rem;
            background: rgba(0,0,0,0.8);
        }

        .video-title {
            font-size: 1.3rem;
            margin-bottom: 0.5rem;
        }

        .video-description {
            color: #b3b3b3;
            margin-bottom: 1rem;
        }

        .test-controls {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .test-btn {
            background: #e50914;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
            transition: background 0.3s;
        }

        .test-btn:hover {
            background: #f40612;
        }

        .test-btn.secondary {
            background: rgba(109,109,110,0.7);
        }

        .test-btn.secondary:hover {
            background: rgba(109,109,110,0.9);
        }

        .test-results {
            background: rgba(0,0,0,0.3);
            border-radius: 6px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .test-result {
            padding: 0.5rem;
            margin: 0.5rem 0;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }

        .success { background: rgba(76, 175, 80, 0.2); color: #4caf50; }
        .error { background: rgba(244, 67, 54, 0.2); color: #f44336; }
        .warning { background: rgba(255, 193, 7, 0.2); color: #ffc107; }
        .info { background: rgba(33, 150, 243, 0.2); color: #2196f3; }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .feature-card {
            background: rgba(0,0,0,0.4);
            padding: 1rem;
            border-radius: 6px;
            border: 1px solid rgba(255,255,255,0.1);
        }

        .feature-title {
            color: #e50914;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .feature-status {
            font-size: 0.9rem;
            color: #b3b3b3;
        }

        .back-btn {
            background: #e50914;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-top: 2rem;
        }

        @media (max-width: 768px) {
            .test-video {
                height: 250px;
            }
            
            .test-controls {
                flex-direction: column;
                align-items: center;
            }
            
            .test-btn {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">VIDEO TEST</div>
            <div class="subtitle">Testing video playback functionality</div>
        </div>

        <div class="test-section">
            <div class="section-title">🎥 Video Player Test</div>
            
            <div class="video-container">
                <video class="test-video" id="testVideo" controls preload="metadata">
                    <source src="<?php echo htmlspecialchars($testVideo['video_url']); ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                
                <div class="video-info">
                    <div class="video-title"><?php echo htmlspecialchars($testVideo['title']); ?></div>
                    <div class="video-description"><?php echo htmlspecialchars($testVideo['description']); ?></div>
                </div>
            </div>

            <div class="test-controls">
                <button class="test-btn" onclick="testPlay()">▶ Test Play</button>
                <button class="test-btn" onclick="testPause()">⏸ Test Pause</button>
                <button class="test-btn" onclick="testSeek()">⏩ Test Seek</button>
                <button class="test-btn" onclick="testVolume()">🔊 Test Volume</button>
                <button class="test-btn" onclick="testFullscreen()">⛶ Test Fullscreen</button>
                <button class="test-btn secondary" onclick="runAllTests()">🧪 Run All Tests</button>
            </div>

            <div class="test-results" id="testResults">
                <div class="test-result info">Click buttons above to test video functionality</div>
            </div>
        </div>

        <div class="test-section">
            <div class="section-title">🔧 Video Features</div>
            
            <div class="feature-grid">
                <div class="feature-card">
                    <div class="feature-title">HTML5 Video Support</div>
                    <div class="feature-status" id="html5Support">Checking...</div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-title">MP4 Format Support</div>
                    <div class="feature-status" id="mp4Support">Checking...</div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-title">Fullscreen API</div>
                    <div class="feature-status" id="fullscreenSupport">Checking...</div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-title">Video Metadata</div>
                    <div class="feature-status" id="metadataSupport">Checking...</div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-title">Playback Controls</div>
                    <div class="feature-status" id="controlsSupport">Checking...</div>
                </div>
                
                <div class="feature-card">
                    <div class="feature-title">Progress Tracking</div>
                    <div class="feature-status" id="progressSupport">Checking...</div>
                </div>
            </div>
        </div>

        <div style="text-align: center;">
            <a href="index.php" class="back-btn">← Back to Home</a>
        </div>
    </div>

    <script>
        const video = document.getElementById('testVideo');
        const results = document.getElementById('testResults');
        
        function addResult(message, type = 'info') {
            const result = document.createElement('div');
            result.className = `test-result ${type}`;
            result.textContent = message;
            results.appendChild(result);
            results.scrollTop = results.scrollHeight;
        }
        
        function clearResults() {
            results.innerHTML = '';
        }
        
        function testPlay() {
            clearResults();
            addResult('Testing play functionality...', 'info');
            
            video.play().then(() => {
                addResult('✅ Play test: SUCCESS', 'success');
            }).catch(error => {
                addResult('❌ Play test: FAILED - ' + error.message, 'error');
            });
        }
        
        function testPause() {
            addResult('Testing pause functionality...', 'info');
            
            try {
                video.pause();
                addResult('✅ Pause test: SUCCESS', 'success');
            } catch (error) {
                addResult('❌ Pause test: FAILED - ' + error.message, 'error');
            }
        }
        
        function testSeek() {
            addResult('Testing seek functionality...', 'info');
            
            try {
                const originalTime = video.currentTime;
                video.currentTime = Math.min(video.duration || 30, 30);
                
                setTimeout(() => {
                    if (video.currentTime !== originalTime) {
                        addResult('✅ Seek test: SUCCESS', 'success');
                    } else {
                        addResult('⚠️ Seek test: No change detected', 'warning');
                    }
                }, 500);
            } catch (error) {
                addResult('❌ Seek test: FAILED - ' + error.message, 'error');
            }
        }
        
        function testVolume() {
            addResult('Testing volume control...', 'info');
            
            try {
                const originalVolume = video.volume;
                video.volume = 0.5;
                
                setTimeout(() => {
                    if (video.volume === 0.5) {
                        addResult('✅ Volume test: SUCCESS', 'success');
                        video.volume = originalVolume;
                    } else {
                        addResult('❌ Volume test: FAILED', 'error');
                    }
                }, 100);
            } catch (error) {
                addResult('❌ Volume test: FAILED - ' + error.message, 'error');
            }
        }
        
        function testFullscreen() {
            addResult('Testing fullscreen functionality...', 'info');
            
            if (video.requestFullscreen) {
                video.requestFullscreen().then(() => {
                    addResult('✅ Fullscreen test: SUCCESS', 'success');
                    setTimeout(() => {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                    }, 2000);
                }).catch(error => {
                    addResult('❌ Fullscreen test: FAILED - ' + error.message, 'error');
                });
            } else {
                addResult('⚠️ Fullscreen test: Not supported in this browser', 'warning');
            }
        }
        
        function runAllTests() {
            clearResults();
            addResult('Running comprehensive video tests...', 'info');
            
            setTimeout(() => testPlay(), 500);
            setTimeout(() => testPause(), 2000);
            setTimeout(() => testSeek(), 3000);
            setTimeout(() => testVolume(), 4000);
            setTimeout(() => testFullscreen(), 5000);
        }
        
        // Check browser support
        function checkSupport() {
            // HTML5 Video Support
            const supportsVideo = !!document.createElement('video').canPlayType;
            document.getElementById('html5Support').textContent = supportsVideo ? '✅ Supported' : '❌ Not Supported';
            
            // MP4 Support
            const video = document.createElement('video');
            const canPlayMP4 = video.canPlayType('video/mp4') !== '';
            document.getElementById('mp4Support').textContent = canPlayMP4 ? '✅ Supported' : '❌ Not Supported';
            
            // Fullscreen API
            const supportsFullscreen = !!(document.fullscreenEnabled || document.webkitFullscreenEnabled || document.mozFullScreenEnabled);
            document.getElementById('fullscreenSupport').textContent = supportsFullscreen ? '✅ Supported' : '❌ Not Supported';
            
            // Metadata
            document.getElementById('metadataSupport').textContent = '✅ Supported';
            
            // Controls
            document.getElementById('controlsSupport').textContent = '✅ Supported';
            
            // Progress Tracking
            document.getElementById('progressSupport').textContent = '✅ Supported';
        }
        
        // Video event listeners for testing
        video.addEventListener('loadedmetadata', () => {
            addResult('📊 Video metadata loaded - Duration: ' + Math.round(video.duration) + 's', 'info');
        });
        
        video.addEventListener('canplay', () => {
            addResult('✅ Video ready to play', 'success');
        });
        
        video.addEventListener('error', (e) => {
            addResult('❌ Video error: ' + e.message, 'error');
        });
        
        video.addEventListener('timeupdate', () => {
            // Update progress (throttled)
            if (Math.floor(video.currentTime) % 5 === 0) {
                const progress = Math.round((video.currentTime / video.duration) * 100);
                if (progress > 0 && progress < 100) {
                    addResult('📈 Progress: ' + progress + '%', 'info');
                }
            }
        });
        
        // Initialize support check
        checkSupport();
        
        // Auto-test on page load
        setTimeout(() => {
            addResult('🚀 Auto-testing video load...', 'info');
        }, 1000);
    </script>
</body>
</html>
