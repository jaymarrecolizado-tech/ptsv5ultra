<?php
/**
 * Login Page
 */

require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: pages/dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (login($username, $password)) {
        header('Location: pages/dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Project Tracking System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        #canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        .login-container {
            position: relative;
            z-index: 10;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <!-- Three.js Particle Background -->
    <div id="canvas-container"></div>
    
    <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-2xl p-8 w-full max-w-md border border-white/20 login-container">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Project Tracking System</h1>
            <p class="text-gray-300 mt-2">Sign in to your account</p>
        </div>
        
        <?php if ($error): ?>
        <div class="bg-red-500/20 border border-red-400/30 text-red-200 px-4 py-3 rounded-lg mb-6">
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="space-y-6">
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Username or Email</label>
                <input type="text" name="username" required 
                       class="w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none transition-colors text-white placeholder-gray-400"
                       placeholder="Enter your username">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-200 mb-2">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-4 py-3 bg-white/20 border border-white/30 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none transition-colors text-white placeholder-gray-400"
                       placeholder="Enter your password">
            </div>
            
            <button type="submit" 
                    class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                Sign In
            </button>
        </form>
        
        <div class="mt-4 text-center text-sm">
            <p class="text-gray-300">Don't have an account? 
                <a href="pages/register.php" class="text-blue-300 hover:text-blue-200 font-medium">Create one</a>
            </p>
        </div>
    </div>

    <!-- Three.js Particle Animation -->
    <script>
        // Particle Animation Script
        const container = document.getElementById('canvas-container');
        
        // Scene setup
        const scene = new THREE.Scene();
        const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
        const renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
        
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        container.appendChild(renderer.domElement);
        
        // Create particles
        const particleCount = 150;
        const particles = new THREE.BufferGeometry();
        const positions = new Float32Array(particleCount * 3);
        const velocities = [];
        
        for (let i = 0; i < particleCount * 3; i += 3) {
            positions[i] = (Math.random() - 0.5) * 20;
            positions[i + 1] = (Math.random() - 0.5) * 20;
            positions[i + 2] = (Math.random() - 0.5) * 10;
            
            velocities.push({
                x: (Math.random() - 0.5) * 0.01,
                y: (Math.random() - 0.5) * 0.01,
                z: (Math.random() - 0.5) * 0.01
            });
        }
        
        particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));
        
        // Particle material
        const particleMaterial = new THREE.PointsMaterial({
            color: 0x60a5fa,
            size: 0.15,
            transparent: true,
            opacity: 0.8,
            blending: THREE.AdditiveBlending
        });
        
        const particleSystem = new THREE.Points(particles, particleMaterial);
        scene.add(particleSystem);
        
        // Create connecting lines
        const lineGeometry = new THREE.BufferGeometry();
        const lineMaterial = new THREE.LineBasicMaterial({
            color: 0x93c5fd,
            transparent: true,
            opacity: 0.2,
            blending: THREE.AdditiveBlending
        });
        
        const maxConnections = 300;
        const linePositions = new Float32Array(maxConnections * 6);
        lineGeometry.setAttribute('position', new THREE.BufferAttribute(linePositions, 3));
        
        const lines = new THREE.LineSegments(lineGeometry, lineMaterial);
        scene.add(lines);
        
        // Mouse interaction
        let mouseX = 0;
        let mouseY = 0;
        
        document.addEventListener('mousemove', (event) => {
            mouseX = (event.clientX / window.innerWidth) * 2 - 1;
            mouseY = -(event.clientY / window.innerHeight) * 2 + 1;
        });
        
        camera.position.z = 10;
        
        // Animation loop
        function animate() {
            requestAnimationFrame(animate);
            
            const positionAttribute = particles.getAttribute('position');
            const posArray = positionAttribute.array;
            
            // Update particle positions
            for (let i = 0; i < particleCount; i++) {
                const idx = i * 3;
                
                posArray[idx] += velocities[i].x;
                posArray[idx + 1] += velocities[i].y;
                posArray[idx + 2] += velocities[i].z;
                
                // Mouse influence
                const dx = mouseX * 5 - posArray[idx];
                const dy = mouseY * 5 - posArray[idx + 1];
                posArray[idx] += dx * 0.001;
                posArray[idx + 1] += dy * 0.001;
                
                // Boundary wrapping
                if (posArray[idx] > 10) posArray[idx] = -10;
                if (posArray[idx] < -10) posArray[idx] = 10;
                if (posArray[idx + 1] > 10) posArray[idx + 1] = -10;
                if (posArray[idx + 1] < -10) posArray[idx + 1] = 10;
                if (posArray[idx + 2] > 5) posArray[idx + 2] = -5;
                if (posArray[idx + 2] < -5) posArray[idx + 2] = 5;
            }
            
            positionAttribute.needsUpdate = true;
            
            // Draw connections
            let lineIndex = 0;
            const linePosArray = lineGeometry.getAttribute('position').array;
            
            for (let i = 0; i < particleCount && lineIndex < maxConnections * 6; i++) {
                for (let j = i + 1; j < particleCount && lineIndex < maxConnections * 6; j++) {
                    const idx1 = i * 3;
                    const idx2 = j * 3;
                    
                    const dx = posArray[idx1] - posArray[idx2];
                    const dy = posArray[idx1 + 1] - posArray[idx2 + 1];
                    const dz = posArray[idx1 + 2] - posArray[idx2 + 2];
                    const dist = Math.sqrt(dx * dx + dy * dy + dz * dz);
                    
                    if (dist < 3) {
                        linePosArray[lineIndex++] = posArray[idx1];
                        linePosArray[lineIndex++] = posArray[idx1 + 1];
                        linePosArray[lineIndex++] = posArray[idx1 + 2];
                        linePosArray[lineIndex++] = posArray[idx2];
                        linePosArray[lineIndex++] = posArray[idx2 + 1];
                        linePosArray[lineIndex++] = posArray[idx2 + 2];
                    }
                }
            }
            
            // Clear remaining line positions
            for (let i = lineIndex; i < maxConnections * 6; i++) {
                linePosArray[i] = 0;
            }
            
            lineGeometry.getAttribute('position').needsUpdate = true;
            
            // Rotate scene slightly
            scene.rotation.y += 0.001;
            
            renderer.render(scene, camera);
        }
        
        animate();
        
        // Handle resize
        window.addEventListener('resize', () => {
            camera.aspect = window.innerWidth / window.innerHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(window.innerWidth, window.innerHeight);
        });
    </script>
</body>
</html>
