import sys
import face_recognition
import os

# Paths
known_faces_dir = os.path.dirname(__file__) + '/known_faces'
captured_image_path = sys.argv[1]

# Load captured image
captured_image = face_recognition.load_image_file(captured_image_path)
captured_encoding = face_recognition.face_encodings(captured_image)

if not captured_encoding:
    print('NO_FACE')
    sys.exit(1)

captured_encoding = captured_encoding[0]

# Load known faces
for filename in os.listdir(known_faces_dir):
    if filename.endswith('.jpg') or filename.endswith('.jpeg') or filename.endswith('.png'):
        known_image = face_recognition.load_image_file(os.path.join(known_faces_dir, filename))
        known_encoding = face_recognition.face_encodings(known_image)
        if known_encoding:
            known_encoding = known_encoding[0]

            # Compare
            result = face_recognition.compare_faces([known_encoding], captured_encoding, tolerance=0.5)

            if result[0]:
                print('MATCH')
                sys.exit(0)

print('NO_MATCH')
sys.exit(1)
