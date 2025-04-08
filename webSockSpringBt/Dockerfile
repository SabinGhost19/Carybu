# Use an official OpenJDK runtime as a parent image
FROM openjdk:23-slim

# Set the working directory in the container
WORKDIR /app

# Copy the Maven target directory (compiled classes and dependencies)
COPY target/*.jar app.jar

# Expose the port the app runs on
EXPOSE 8080

# Run the jar file
ENTRYPOINT ["java", "-jar", "/app/app.jar"]