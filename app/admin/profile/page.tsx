"use client"

import { useEffect, useState } from "react"
import { useAuth } from "@/lib/auth-context"
import { useRouter } from "next/navigation"
import { AdminNav } from "@/components/admin-nav"
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { User, Mail, Phone, Building2, Save } from "lucide-react"

export default function AdminProfile() {
  const { user, isLoading } = useAuth()
  const router = useRouter()
  const [name, setName] = useState("")
  const [email, setEmail] = useState("")
  const [phone, setPhone] = useState("")
  const [professionalDetails, setProfessionalDetails] = useState("")
  const [isSaving, setIsSaving] = useState(false)

  useEffect(() => {
    if (!isLoading && (!user || (user.role !== "admin" && user.role !== "manager"))) {
      router.push("/")
    }
    if (user) {
      setName(user.name || "")
      setEmail(user.email || "")
      setPhone(user.phone || "")
      setProfessionalDetails(user.professionalDetails || "")
    }
  }, [user, isLoading, router])

  if (isLoading || !user) {
    return null
  }

  const handleSave = async () => {
    setIsSaving(true)
    // Simulate API call
    await new Promise((resolve) => setTimeout(resolve, 1000))
    setIsSaving(false)
    alert("Profile updated successfully!")
  }

  return (
    <div className="min-h-screen bg-muted/30">
      <AdminNav active="profile" />

      <main className="container mx-auto px-4 py-8">
        <div className="mb-8">
          <h1 className="text-4xl font-bold mb-2 text-balance">User Profile</h1>
          <p className="text-muted-foreground text-lg">Manage your account information and professional details</p>
        </div>

        <div className="max-w-3xl">
          <Card>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <User className="w-5 h-5" />
                Profile Information
              </CardTitle>
              <CardDescription>Update your personal and professional information</CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
              <div className="space-y-2">
                <Label htmlFor="name" className="flex items-center gap-2">
                  <User className="w-4 h-4" />
                  Full Name
                </Label>
                <Input id="name" value={name} onChange={(e) => setName(e.target.value)} placeholder="Enter your name" />
              </div>

              <div className="space-y-2">
                <Label htmlFor="email" className="flex items-center gap-2">
                  <Mail className="w-4 h-4" />
                  Email Address
                </Label>
                <Input
                  id="email"
                  type="email"
                  value={email}
                  onChange={(e) => setEmail(e.target.value)}
                  placeholder="Enter your email"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="phone" className="flex items-center gap-2">
                  <Phone className="w-4 h-4" />
                  Phone Number
                </Label>
                <Input
                  id="phone"
                  type="tel"
                  value={phone}
                  onChange={(e) => setPhone(e.target.value)}
                  placeholder="Enter your phone number"
                />
              </div>

              <div className="space-y-2">
                <Label htmlFor="details" className="flex items-center gap-2">
                  <Building2 className="w-4 h-4" />
                  Professional Details
                </Label>
                <Textarea
                  id="details"
                  value={professionalDetails}
                  onChange={(e) => setProfessionalDetails(e.target.value)}
                  placeholder="Enter your professional background and experience"
                  rows={4}
                />
              </div>

              <div className="flex gap-4 pt-4">
                <Button
                  onClick={handleSave}
                  disabled={isSaving}
                  className="bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700"
                >
                  <Save className="w-4 h-4 mr-2" />
                  {isSaving ? "Saving..." : "Save Changes"}
                </Button>
                <Button variant="outline" onClick={() => router.push("/admin")}>
                  Cancel
                </Button>
              </div>
            </CardContent>
          </Card>

          <Card className="mt-6">
            <CardHeader>
              <CardTitle>Account Information</CardTitle>
              <CardDescription>Your account details and role</CardDescription>
            </CardHeader>
            <CardContent>
              <div className="space-y-3">
                <div className="flex justify-between py-2 border-b">
                  <span className="text-muted-foreground">User ID</span>
                  <span className="font-medium">{user.id}</span>
                </div>
                <div className="flex justify-between py-2 border-b">
                  <span className="text-muted-foreground">Role</span>
                  <span className="font-medium capitalize">{user.role}</span>
                </div>
                <div className="flex justify-between py-2">
                  <span className="text-muted-foreground">Restaurant ID</span>
                  <span className="font-medium">{user.restaurantId || "N/A"}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </main>
    </div>
  )
}
