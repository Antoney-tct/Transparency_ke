<?php
require_once 'db_connect.php';

function getAllProjects() {
    global $conn;
    
    $sql = "SELECT p.*, c.name as category_name, co.name as county_name 
            FROM projects p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN counties co ON p.county_id = co.id
            ORDER BY p.created_at DESC";
            
    $result = $conn->query($sql);
    
    $projects = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
    }
    
    return $projects;
}

function getProjectsByStatus($status) {
    global $conn;
    
    $status = sanitize($conn, $status);
    
    $sql = "SELECT p.*, c.name as category_name, co.name as county_name 
            FROM projects p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN counties co ON p.county_id = co.id
            WHERE p.status = '$status'
            ORDER BY p.created_at DESC";
            
    $result = $conn->query($sql);
    
    $projects = array();
    
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
    }
    
    return $projects;
}

function getProjectById($id) {
    global $conn;
    
    $id = (int)$id;
    
    $sql = "SELECT p.*, c.name as category_name, co.name as county_name 
            FROM projects p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN counties co ON p.county_id = co.id
            WHERE p.id = $id";
            
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $project = $result->fetch_assoc();
        
        // Get project updates
        $sql = "SELECT pu.*, u.full_name as posted_by_name 
                FROM project_updates pu 
                LEFT JOIN users u ON pu.posted_by = u.id 
                WHERE pu.project_id = $id
                ORDER BY pu.update_date DESC";
                
        $updatesResult = $conn->query($sql);
        $updates = array();
        
        if ($updatesResult->num_rows > 0) {
            while($updateRow = $updatesResult->fetch_assoc()) {
                $updates[] = $updateRow;
            }
        }
        
        $project['updates'] = $updates;
        
        // Get approved comments
        $sql = "SELECT * FROM comments 
                WHERE project_id = $id AND status = 'approved'
                ORDER BY created_at DESC";
                
        $commentsResult = $conn->query($sql);
        $comments = array();
        
        if ($commentsResult->num_rows > 0) {
            while($commentRow = $commentsResult->fetch_assoc()) {
                $comments[] = $commentRow;
            }
        }
        
        $project['comments'] = $comments;
        
        return $project;
    }
    
    return null;
}
?>