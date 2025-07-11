pipeline {
    agent any

    environment {
        GITHUB_CREDENTIALS_ID = 'faveobot' // Set your GitHub credentials ID here
        MYSQL_CREDENTIALS_ID = 'mysql_credentials_id'
        SONARQUBE_SERVER = 'SonarqubeFaveoInvoicingCommunity'
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
                    COMPOSER_MEMORY_LIMIT=-1 php artisan test --coverage-php=storage/sonarqube/clover.xml
                    """
                    }
                }
            }
        }

        stage('SonarQube Analysis') {
            steps {
                withSonarQubeEnv("${SONARQUBE_SERVER}") {
                    sh """
                        sonar-scanner \
                          -Dsonar.projectKey=faveo-invoicing-community \
                          -Dsonar.sources=. \
                          -Dsonar.exclusions=**/storage/**,**/vendor/**,**/node_modules/**,**/.scannerwork/**,**/public/uploads/**,**/*.log,**/*.cache,**/*.html,**/*.md,**/*.xml,**/*.sh \
                          -Dsonar.php.coverage.reportPaths=storage/sonarqube/clover.xml \
                          -Dsonar.javascript.lcov.reportPaths=coverage/lcov.info \
                          -Dsonar.host.url=$SONAR_HOST_URL \
                          -Dsonar.token=$SONAR_AUTH_TOKEN \
                          -Dsonar.sourceEncoding=UTF-8 \
                          -Dsonar.inclusions=**/*.php,**/*.vue,**/*.js,**/*.css
                    """
                }
            }
        }
        stage('Quality Gate Check and PR Comment') {
            steps {
                withSonarQubeEnv("${SONARQUBE_SERVER}") {
                    script {
                        def repoName = scm.userRemoteConfigs[0].url.tokenize('/').last().replace('.git', '')
                        def prNumber = env.CHANGE_ID ?: ''
                        if (!prNumber) {
                            prNumber = sh(script: '''
                                curl -s -u ${GITHUB_USER}:${GITHUB_TOKEN} \
                                "https://api.github.com/repos/faveosuite/${repoName}/pulls?head=${GITHUB_USER}:${env.BRANCH_NAME}" \
                                | jq '.[0].number' | tr -d '"'
                            ''', returnStdout: true).trim()
                        }
                        sh '''#!/bin/bash
                        echo "Fetching SonarQube issues for branch ${BRANCH_NAME}..."
                        ISSUES=$(curl -s -u ${SONAR_TOKEN}: \
                          "${SONAR_HOST_URL}/api/issues/search?projectKeys=faveo-invoicing-community&branch=${BRANCH_NAME}&resolved=false&isNew=true")
                        echo "Raw SonarQube issues:"
                        echo "$ISSUES"
                        COUNT=$(echo "$ISSUES" | jq '.total')
                        echo "Total new issues: $COUNT"
                        if [ "$COUNT" -eq 0 ]; then
                            echo "✅ No new issues found. Skipping comment."
                            exit 0
                        fi
                        echo "$ISSUES" | jq -c '.issues[]' | while read issue; do
                            filePath=$(echo "$issue" | jq -r '.component' | sed 's/.*://')
                            lineNumber=$(echo "$issue" | jq -r '.line // 1')
                            message=$(echo "$issue" | jq -r '.message')
                            rule=$(echo "$issue" | jq -r '.rule')
                            ruleUrl="${SONAR_HOST_URL}/coding_rules?rule_key=$rule"
                            ruleData=$(curl -s -u ${SONAR_TOKEN}: "${SONAR_HOST_URL}/api/rules/show?key=$rule")
                            why=$(echo "$ruleData" | jq -r '.rule.htmlDesc // "No description available."' | sed 's/<[^>]*>//g' | tr -d '\\n')
                            how=$(echo "$ruleData" | jq -r '.rule.htmlNoncompliantExample // "No code suggestion available."' | sed 's/<[^>]*>//g' | tr -d '\\r')
                            body="### SonarQube Issue
                            **Where is the issue?**: ${filePath}:${lineNumber}
                            **Why is this an issue?**: ${message}
                            **Explanation**: ${why}
                            **How can I fix it?**: ${how}
                            **More info**: [${rule}](${ruleUrl})"
                            comment=$(jq -n --arg body "$body" '{ body: $body }')
                            echo "📌 Commenting on PR #${prNumber}..."
                            curl -s -X POST -u ${GITHUB_USER}:${GITHUB_TOKEN} \
                                 -H "Content-Type: application/json" \
                                 -d "$comment" \
                                 "https://api.github.com/repos/faveosuite/${repoName}/issues/${prNumber}/comments"
                        done
                        '''
                    }
                }
            }
        }

        stage('Wait for SonarQube Quality Gate') {
            steps {
                timeout(time: 2, unit: 'MINUTES') {
                    waitForQualityGate abortPipeline: true
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
