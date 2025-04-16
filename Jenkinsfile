pipeline {
    agent any

    environment {
        GITHUB_CREDENTIALS_ID = 'faveobot' // Set your GitHub credentials ID here
        MYSQL_CREDENTIALS_ID = 'mysql_credentials_id'
    }

    stages {
        stage('Checkout') {
            steps {
                // Checkout the code from the pull request branch
                checkout scm
            }
        }

        stage('Composer Dump-Autoload') {
            steps {
                // Run composer dump-autoload
                sh 'composer dump-autoload'
            }
        }

        stage('Database Setup & Testing') {
            steps {
                script {
                    def buildNumber = env.BUILD_NUMBER

                       withCredentials([
                          usernamePassword(credentialsId: MYSQL_CREDENTIALS_ID, usernameVariable: 'DB_USER', passwordVariable: 'DB_PASS')
                       ]){

                    // Create SQL file and setup the database
                    sh """
                    echo "Creating database billing_${buildNumber}" && \
                    echo "CREATE DATABASE billing_${buildNumber};" > /var/lib/jenkins/automation/billing_${buildNumber}.sql && \
                    echo "DROP DATABASE IF EXISTS billing_${buildNumber};" >> /var/lib/jenkins/automation/billing_${buildNumber}.sql && \
                    mysql -u ${DB_USER} -p${DB_PASS} < /var/lib/jenkins/automation/billing_${buildNumber}.sql && \
                    php artisan optimize:clear && \
                    php artisan testing-setup --username=${DB_USER} --password=${DB_PASS} --database=billing_${buildNumber} && \
                    COMPOSER_MEMORY_LIMIT=-1 php artisan test
                    """
                    }
                }
            }
        }
    }

    post {
        always {
            script {
                def commitSha = env.GIT_COMMIT
                def repoUrl = scm.userRemoteConfigs[0].url
                def repoName = repoUrl.tokenize('/').last().replace('.git', '')

                withCredentials([usernamePassword(credentialsId: GITHUB_CREDENTIALS_ID, usernameVariable: 'GITHUB_USER', passwordVariable: 'GITHUB_TOKEN')]) {
                    def status = currentBuild.currentResult == 'SUCCESS' ? 'success' : 'failure'
                    def statusUrl = "${env.BUILD_URL}"
                    def description = currentBuild.currentResult == 'SUCCESS' ? 'Build successful' : 'Build failed'
                    def prNumber = env.CHANGE_ID

                    // Fallback to get PR number using branch name if CHANGE_ID is null
                    if (!prNumber) {
                        prNumber = sh(script: """
                            curl -s -u ${GITHUB_USER}:${GITHUB_TOKEN} \
                            "https://api.github.com/repos/faveosuite/${repoName}/pulls?head=${GITHUB_USER}:${env.BRANCH_NAME}" \
                            | jq '.[0].number' | tr -d '"'
                        """, returnStdout: true).trim()
                    }

                    // Update build status on GitHub
                    sh """
                    curl -u ${GITHUB_USER}:${GITHUB_TOKEN} \
                         -d '{"state": "${status}", "target_url": "${statusUrl}", "description": "${description}", "context": "Jenkins"}' \
                         https://api.github.com/repos/faveosuite/${repoName}/statuses/${commitSha}
                    """

                    // Close the pull request if the build fails
                    if (currentBuild.currentResult != 'SUCCESS') {
                        echo "Closing the PR due to build failure..."
                        sh """
                        curl -X PATCH -u ${GITHUB_USER}:${GITHUB_TOKEN} \
                             -d '{"state": "closed"}' \
                             https://api.github.com/repos/faveosuite/${repoName}/pulls/${prNumber}
                        """
                    }
                }
            }

            // Clean up by dropping the test database
            script {
                def buildNumber = env.BUILD_NUMBER

                 withCredentials([
                    usernamePassword(credentialsId: MYSQL_CREDENTIALS_ID, usernameVariable: 'DB_USER', passwordVariable: 'DB_PASS')
                 ]){

                sh """
                echo "Dropping database billing_${buildNumber}" && \
                mysql -u ${DB_USER} -p${DB_PASS} -e "DROP DATABASE IF EXISTS billing_${buildNumber};" && \
                rm -rf /var/lib/jenkins/automation/billing_${buildNumber}.sql
                """
                }
            }
        }
    }
}
