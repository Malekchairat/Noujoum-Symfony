import sys
import face_recognition
import os

# Suppose we store known faces in public/known_faces/<email>.jpg

KNOWN_FACES_DIR = os.path.join(os.path.dirname(__file__), '..', 'known_faces')

def main():
    if len(sys.argv) < 2:
        print("")
        sys.exit(1)

    input_image_path = sys.argv[1]

    # Load unknown image
    unknown_image = face_recognition.load_image_file(input_image_path)
    unknown_encodings = face_recognition.face_encodings(unknown_image)

    if not unknown_encodings:
        print("")
        sys.exit(1)

    unknown_encoding = unknown_encodings[0]

    # Search known faces
    for filename in os.listdir(KNOWN_FACES_DIR):
        if filename.endswith('.jpg') or filename.endswith('.png'):
            known_image = face_recognition.load_image_file(os.path.join(KNOWN_FACES_DIR, filename))
            known_encodings = face_recognition.face_encodings(known_image)
            if known_encodings:
                match = face_recognition.compare_faces([known_encodings[0]], unknown_encoding)[0]
                if match:
                    email = filename.rsplit('.', 1)[0]
                    print(email)
                    return

    print("")

if __name__ == "__main__":
    main()
